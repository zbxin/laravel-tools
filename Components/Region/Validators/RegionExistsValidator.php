<?php

namespace Extend\Region\Validators;

use Extend\Contracts\Base\Validator;
use Extend\Region\Region;

class RegionExistsValidator extends Validator
{

    /**
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validator($attribute, $value, $parameters, $validator)
    {
        return !empty($value) ? Region::whereId($value)->exists() : true;
    }
}