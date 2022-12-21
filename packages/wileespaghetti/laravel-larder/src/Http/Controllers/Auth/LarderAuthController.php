<?php

namespace Larder\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\SocialIdentity;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Pantry\User;
use function redirect;


/**
 * TODO
 * handle 401 error
 */
class LarderAuthController extends Controller
{
    private static string $provider = 'larder';

    /**
     * Where to redirect users after login.
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    public function redirectToProvider() {
        return Socialite::with(static::$provider)->redirect();
    }

    public function handleProviderCallback() {
        $user = Socialite::with(static::$provider)->stateless()->user();

        $authUser = $this->findOrCreateUser($user, static::$provider);
        Auth::login($authUser);

        foreach($authUser->identities as $social) {
            $isCurrentProvider = $social->provider_name === static::$provider;
            $hasSameAccessToken = $social->access_token === $user->accessTokenResponseBody['access_token'];
            $hasSameRefreshToken = $social->refresh_token === $user->accessTokenResponseBody['refresh_token'];
            $hasNewTokens = !($hasSameAccessToken && $hasSameRefreshToken);
            if ($isCurrentProvider && $hasNewTokens) {
                $social->refresh_token = $user->accessTokenResponseBody['refresh_token'];
                $social->access_token = $user->accessTokenResponseBody['access_token'];
                $social->save();
            }
        }

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
                'refresh_token' => $providerUser->accessTokenResponseBody['refresh_token'],
                'access_token' => $providerUser->accessTokenResponseBody['access_token'],
            ]);

            return $user;
        }
    }
}
