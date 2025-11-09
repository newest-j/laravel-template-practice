<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Auth\Notifications\ResetPassword;


// use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Http\Responses\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use App\Http\Responses\LogoutResponse;



// the fortify route are register in the web route from fortify

// so services providers are use to register and boot an application services
// an application service is anything that the app need at startup to handle some request

// so basically it is so that the services are already prepared and booted because it 
//  required to load from the config which will take time if it is to run after the request 
// so it should be in the services where it is warm and ready


// so for socialite it will teach the services how to talk to the provider like google 
// and then the controller will handle the route

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // so the authenticateUsing is for like check when the user has been created how to let them in base on a condition 
        // while the
        // createUsersUsing is use to do the validation and cfeate the data to the database

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            try {
                // 1. Validate input
                Validator::make($request->only('email', 'password'), [
                    'email' => ['required', 'email'],
                    'password' => ['required', 'string'],
                ])->validate();

                // 2. Attempt login
                // fortify handles this
                // $credentials = $request->only('email', 'password');

                // if (Auth::attempt($credentials)) {
                //     // 3. Login successful, regenerate session
                //     $request->session()->regenerate();
                //     $request->session()->regenerateToken();

                //     return $request->user(); // Fortify will call LoginResponse
                // }

                // 4. Invalid credentials

                $user = User::where('email', $request->email)->first();

                return $user && Hash::check($request->password, $user->password)
                    ? $user
                    : null; // Fortify handles guard->login() + session rotation
            } catch (\Throwable $e) {
                // 5. Handle unexpected errors
                report($e);

                if (app()->environment('local')) {
                    throw $e; // show full trace in dev
                }

                abort(response()->json([
                    'success' => false,
                    'message' => 'Login failed',
                ], 500));
            }
        });



        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);


        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        // Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // For SPA password reset link (Laravel 10/11+)
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontend = env('FRONTEND_URL', 'http://localhost:5173');
            return $frontend . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });


        RateLimiter::for('login', function (Request $request) {
            // the transliterate is a string helper that convert text into a safe ASCII chracter
            // like ó becomes o and Á becomes A
            // the username by default is the email
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

    }
}
