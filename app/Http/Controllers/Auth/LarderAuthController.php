<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class LarderAuthController extends Controller
{
    public function redirectToProvider() {
        return Socialite::with('larder')->redirect();
    }

    public function handleProviderCallback() {
        $user = Socialite::with('larder')->stateless()->user();

        $users = User::where(['email' => $user->getEmail()])->first();
        if ($users) {
            Auth::login($users);
            return redirect('/home');
        } else {
            $user = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'image' => $user->getAvatar(),
                'provider_id' => $user->getId(),
                'provider' => 'larder',
            ]);
            return redirect()->route('home');
        }
    }
}
