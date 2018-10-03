<?php

namespace ZhiEq\Region;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Region
 *
 * @property int $id
 * @property string $name
 * @property int $level
 * @property int $parent
 * @property string $code
 * @property string $phone
 * @method static \Illuminate\Database\Query\Builder|Region whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|Region whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Region whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|Region whereName($value)
 * @method static \Illuminate\Database\Query\Builder|Region whereParent($value)
 * @method static \Illuminate\Database\Query\Builder|Region wherePhone($value)
 */
class Region extends Model
{
    const LEVEL_PROVINCE = 1;
    const LEVEL_CITY = 2;
    const LEVEL_COUNTY = 3;

    protected $table = 'region';

    protected $connection = 'region';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        config(['database.connections.region' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/region.db',
        ]]);
    }

    /**
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */

    public static function provinceList()
    {
        return self::whereLevel(1)->get();
    }

    /**
     * @param null $province_id
     * @return \Illuminate\Support\Collection
     */

    public static function cityList($province_id = null)
    {
        $province_id = $province_id === null ? self::provinceList()->first()->id : $province_id;
        return self::whereParent($province_id)->whereLevel(2)->get();
    }

    /**
     * @param null $city_id
     * @return \Illuminate\Support\Collection
     */

    public static function countyList($city_id = null)
    {
        $city_id = $city_id === null ? self::cityList()->first()->id : $city_id;
        return self::whereLevel(3)->whereParent($city_id)->get();
    }

    /**
     *
     * @param $id
     * @return mixed|string
     */

    public static function getName($id)
    {
        try {
            return cache()->tags(['region', 'model', 'name'])->remember($id, Carbon::now()->addHours(1), function () use ($id) {
                $model = self::whereId($id)->first();
                return empty($model) ? '' : $model->name;
            });
        } catch (\Exception $exception) {
            return '';
        }
    }

}
