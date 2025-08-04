<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);

                $user->email_verified_at = now();
                $user->save();
            }

            Auth::login($user);

            return redirect()->intended('/dashboard')->with('success', 'Logged in successfully.');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors('Failed to login with Google.');
        }
    }

}
