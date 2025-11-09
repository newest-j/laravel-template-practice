<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Handle registration with JSON + session.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        try {
            // 1. Validate (this throws ValidationException automatically if invalid)
            // the input represent the request coming

            Validator::make($input, [
                'name' => ['required', 'string', 'max:255'],
                // this is a rule class way of doing validation with the []
                // 'email' => 'required|email|unique:users,email'
                // that is a string way

                // it knows its email with the key so the key and the column in the database
                // should be the same or to be explicit   Rule::unique(User::class,'email'),
                // unique:users,email'
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                // the this->passwordRules is 
                // It defines the validation rules for the password field.
                // coming from the passwordValidationRules.php
                'password' => $this->passwordRules(),
            ])->validate();

            // the validate() runs the validation where the Validator is the facade service and if failed it calls the ValidationException to get the response

            // 2. Create user
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // Dispatch so verification email is sent
            // fortify does this
            // event(new Registered($user));




            // 3. Log them in + regenerate session & CSRF token
            // fortify does this
            // Auth::login($user);
            // request()->session()->regenerate();
            // request()->session()->regenerateToken();

            return $user;
        } catch (\Throwable $e) {
            report($e);

            if (app()->environment('local')) {
                throw $e; // show trace in dev
            }

            abort(response()->json([
                'success' => false,
                'message' => 'Account creation failed',
            ], 500));
        }
    }
}
