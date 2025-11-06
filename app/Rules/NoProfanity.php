<?php

namespace App\Rules;

use App\Services\ProfanityFilterService;
use Illuminate\Contracts\Validation\Rule;

class NoProfanity implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value) || !is_string($value)) {
            return true;
        }

        return !ProfanityFilterService::containsProfanity($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute contains inappropriate language. Please use appropriate language.';
    }
}

