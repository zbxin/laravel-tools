<?php

namespace ZhiEq\Events;

use Illuminate\Validation\ValidationException;

trait EventValidatorTrait
{
    /**
     * @var array
     */

    public $input;

    /**
     * @param array $input
     * @throws ValidationException
     */

    public function validateInput(array $input)
    {
        $this->input = $input;
        \Validator::validate($this->input, $this->rules(), $this->messages());
    }
}
