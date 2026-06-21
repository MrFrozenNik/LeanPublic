<?php

namespace App\Http\Controllers;

use App\Models\DiaryEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class DiaryEntryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $entries = $request->user()
            ->diaryEntries()
            ->with(['dish.ingredients', 'ingredient'])
            ->whereDate('eaten_at', $date)
            ->orderBy('eaten_at')
            ->get();

        $totals = ['kcal' => 0, 'protein' => 0, 'fat' => 0, 'carb' => 0];

        foreach ($entries as $entry) {
            $entryTotals = $entry->totals;
            $totals['kcal'] += $entryTotals['kcal'];
            $totals['protein'] += $entryTotals['protein'];
            $totals['fat'] += $entryTotals['fat'];
            $totals['carb'] += $entryTotals['carb'];
        }

        $dishes = $request->user()->dishes()->orderBy('name')->get();
        $ingredients = $request->user()->ingredients()->orderBy('name')->get();

        return view('diary.index', compact('entries', 'totals', 'date', 'dishes', 'ingredients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item' => 'required|string',
            'grams' => 'required|numeric|min:0.1|max:99999.99',
            'eaten_at' => 'required|date',
        ]);

        [$type, $id] = explode(':', $data['item'], 2);

        $payload = [
            'user_id' => $request->user()->id,
            'dish_id' => null,
            'ingredient_id' => null,
            'grams' => $data['grams'],
            'eaten_at' => $data['eaten_at'],
        ];

        if ($type === 'dish') {
            $dish = $request->user()->dishes()->findOrFail($id);
            $payload['dish_id'] = $dish->id;
        } elseif ($type === 'ingredient') {
            $ingredient = $request->user()->ingredients()->findOrFail($id);
            $payload['ingredient_id'] = $ingredient->id;
        } else {
            abort(422, 'Неверный тип записи');
        }

        $entry = DiaryEntry::create($payload);

        Redis::publish('diary.' . $request->user()->id, json_encode([
            'event' => 'entry.created',
            'client_id' => $request->user()->id,
            'entry_id' => $entry->id,
        ]));

        return redirect()->route('diary.index', ['date' => $data['eaten_at']])
            ->with('success', 'Запись добавлена в дневник');
    }

    public function destroy(Request $request, DiaryEntry $diaryEntry)
    {
        Gate::authorize('delete', $diaryEntry);

        $entryId = $diaryEntry->id;
        $userId = $diaryEntry->user_id;
        $date = $diaryEntry->eaten_at->toDateString();

        $diaryEntry->delete();

        Redis::publish('diary.' . $userId, json_encode([
            'event' => 'entry.deleted',
            'client_id' => $userId,
            'entry_id' => $entryId,
        ]));

        return redirect()->route('diary.index', ['date' => $date])
            ->with('success', 'Запись удалена');
    }
}
