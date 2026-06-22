<?php

namespace App\Http\Controllers;

use App\Models\TrainerLink;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerLinkController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'trainer_email' => 'required|email|exists:users,email',
        ], [
            'trainer_email.exists' => 'Пользователь с таким email не найден',
        ]);

        $trainer = User::where('email', $data['trainer_email'])->first();

        if ($trainer->id === $request->user()->id) {
            return back()->withErrors(['trainer_email' => 'Нельзя пригласить самого себя']);
        }

        $exists = TrainerLink::where('trainer_id', $trainer->id)
            ->where('client_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['trainer_email' => 'Этот тренер уже добавлен']);
        }

        TrainerLink::create([
            'trainer_id' => $trainer->id,
            'client_id' => $request->user()->id,
        ]);

        return back()->with('success', "Тренер {$trainer->name} добавлен");
    }

    public function destroy(Request $request, TrainerLink $trainerLink)
    {
        abort_unless($trainerLink->client_id === $request->user()->id, 403);

        $trainerLink->delete();

        return back()->with('success', 'Доступ тренера отозван');
    }
}
