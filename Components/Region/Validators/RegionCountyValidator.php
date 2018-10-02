<?php

namespace Extend\Region\Validators;

use Extend\Contracts\Base\Validator;
use Extend\Region\Region;

class RegionCountyValidator extends Validator
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
        if (!$cityKey = $parameters[0]) {
            return false;
        }
        $data = $validator->getData();
        if (!isset($data[$cityKey])) {
            return false;
        }
        return Region::whereId($value)->whereLevel(Region::LEVEL_COUNTY)->whereParent($data[$cityKey])->exists();
    }
}