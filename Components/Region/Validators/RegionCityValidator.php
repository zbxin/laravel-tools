<?php

namespace ZhiEq\Region\Validators;

use ZhiEq\Contracts\Validator;
use ZhiEq\Region\Region;

class RegionCityValidator extends Validator
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
        if (!$provinceKey = $parameters[0]) {
            return false;
        }
        $data = $validator->getData();
        if (!isset($data[$provinceKey])) {
            return false;
        }
        return Region::whereId($value)->whereLevel(Region::LEVEL_CITY)->whereParent($data[$provinceKey])->exists();
    }
}
