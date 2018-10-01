<?php

namespace Extend\Region\Validators;

use Extend\Contracts\Base\Validator;
use Extend\Region\Region;

class RegionLevelValidator extends Validator
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
        if(empty($value)){
            return true;
        }
        $level = (int)$parameters[0];
        if ($level <= 0) {
            return false;
        }
        if (!$region = Region::whereId($value)->first()) {
            return false;
        }
        return (int)$region->level === $level;
    }
}