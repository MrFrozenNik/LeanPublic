<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
    {
        $githubUser = Socialite::driver('github')->user();

        $user = User::firstOrNew(['email' => $githubUser->getEmail()]);

        $user->github_id = $githubUser->getId();
        $user->name      = $githubUser->getName() ?: $githubUser->getNickname();

        if (!$user->exists) {
            $user->password = bcrypt(uniqid());
        }

        $user->save();

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }
}
