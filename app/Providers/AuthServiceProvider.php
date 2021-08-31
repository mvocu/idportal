<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\SessionGuard;
use Laravel\Socialite\Contracts\User;
use STS\SocialiteAuth\Facades\SocialiteAuth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        SessionGuard::macro("attemptFromSocialite", function (User $user, $socialiteFieldName) {

            session()->put('socialite-auth.user', $user->toArray());
            
            $modelId = $user[$socialiteFieldName];
            $modelId = str_replace("zstrebotov.cz", "zstrebotov.local", $modelId);

            // First try to find a match
            $userModel = $this->provider->retrieveByCredentials([
                "userprincipalname" => $modelId
            ]);

            // No match? See if we have a custom handler for new users
            if (! $userModel) {
                $userModel = SocialiteAuth::handleNewUser($user);
            }

            if ($this->shouldLoginSocialite($userModel)) {
                $this->login($userModel);
                return true;
            }

            return false;
        });

        //
    }
}
