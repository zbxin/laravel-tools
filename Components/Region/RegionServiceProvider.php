<?php

namespace ZhiEq\Region;

use Illuminate\Support\ServiceProvider;

class RegionServiceProvider extends ServiceProvider
{
    protected $defer = true;

    protected $validators = [
        'region_exists',
        'region_level',
        'region_province',
        'region_province_exists',
        'region_city',
        'region_city_exists',
        'region_county',
        'region_county_exists'
    ];

    /**
     * @return \Illuminate\Validation\Factory
     */

    protected function validator()
    {
        return $this->app['validator'];
    }

    public function boot()
    {
        foreach ($this->validators as $validator) {
            $this->validator()->extend($validator, 'ZhiEq\Region\Validators\\' . studly_case($validator) . 'Validator@validator');
        }
    }
}
