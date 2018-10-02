<?php

namespace ZhiEq\Region;

trait AddressText
{

    /**
     * 获取省份名称
     *
     * @return mixed|string
     */

    public function getProvinceNameAttribute()
    {
        $provinceAttribute = isset($this->province_attribute) ? $this->province_attribute : 'province_id';
        return isset($this->$provinceAttribute) ? Region::getName($this->$provinceAttribute) : null;
    }

    /**
     * 获取城市名称
     *
     * @return mixed|string
     */

    public function getCityNameAttribute()
    {
        $cityAttribute = isset($this->city_attribute) ? $this->city_attribute : 'city_id';
        return isset($this->$cityAttribute) ? Region::getName($this->$cityAttribute) : null;
    }

    /**
     * 获取县区名称
     *
     * @return mixed|string
     */

    public function getCountyNameAttribute()
    {
        $countyAttribute = isset($this->county_attribute) ? $this->county_attribute : 'county_id';
        return isset($this->$countyAttribute) ? Region::getName($this->$countyAttribute) : null;
    }

    /**
     * 获取地址
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return isset($this->address) ? $this->getAreaAttribute() . $this->address : null;
    }

    /**
     * 获取区域
     *
     * @return string
     */
    public function getAreaAttribute()
    {
        $province = $this->getProvinceNameAttribute() ? $this->getProvinceNameAttribute() : '';
        $city = $this->getCityNameAttribute() ? $this->getCityNameAttribute() : '';
        $county = $this->getCountyNameAttribute() ? $this->getCountyNameAttribute() : '';
        return $province . $city . $county;
    }
}
