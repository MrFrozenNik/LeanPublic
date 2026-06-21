<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class TrainerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $clients = $request->user()
            ->clients()
            ->withCount('diaryEntries')
            ->get();

        $diaries = [];
        foreach ($clients as $client) {
            $diaries[$client->id] = $client->diaryEntries()
                ->with(['dish.ingredients', 'ingredient'])
                ->whereDate('eaten_at', $date)
                ->orderBy('eaten_at')
                ->get();
        }

        return view('trainer.dashboard', compact('clients', 'diaries', 'date'));
    }
}
