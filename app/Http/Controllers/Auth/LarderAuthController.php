<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\SocialIdentity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class LarderAuthController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function redirectToProvider() {
        return Socialite::with('larder')->redirect();
    }

    public function handleProviderCallback() {
        $user = Socialite::with('larder')->stateless()->user();

        $authUser = $this->findOrCreateUser($user, 'larder');
        Auth::login($authUser);
        return redirect($this->redirectTo);
    }

    public function findOrCreateUser($providerUser, $provider) {
        $account = SocialIdentity::whereProviderName($provider)
            ->whereProviderId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $user = User::create([
                'email' => $providerUser->getEmail(),
                'name'  => $providerUser->getName(),
            ]);

            $user->identities()->create([
                'provider_id'   => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }
}
