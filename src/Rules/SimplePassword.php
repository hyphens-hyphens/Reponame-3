<?php

namespace T2G\Common\Rules;

use Illuminate\Contracts\Validation\Rule;

class SimplePassword implements Rule
{
    protected $simplePasswords = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->simplePasswords = [
            'abc123', '123123', '123456', '1234567', '123456789', '123321', '112233', '111111', '222222', '999999', '000000', '888888'
        ];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !in_array($value, $this->simplePasswords);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.simple_password');
    }
}
