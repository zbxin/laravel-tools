<?php

namespace ZhiEq\Contracts;


abstract class Validator
{
    /**
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */

    abstract public function validator($attribute, $value, $parameters, $validator);
}
