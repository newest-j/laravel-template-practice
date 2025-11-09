<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        // so the password::default() is a laravel recommended default password policy
        //  that it should be of a min of 8 character long
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
