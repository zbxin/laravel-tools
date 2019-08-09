<?php

namespace ZhiEq\Utils;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Class SearchKeyword
 * @package ZhiEq\Utils
 */
class SearchKeyword
{
    const SEARCH_KEYWORD_TYPE_LIKE = 'like';//模糊搜索
    const SEARCH_KEYWORD_TYPE_MATCH = 'match';//等于
    const SEARCH_KEYWORD_TYPE_IN = 'in';
    const SEARCH_KEYWORD_TYPE_BETWEEN = 'between';//区间
    const SEARCH_KEYWORD_TYPE_DATE_BETWEEN = 'date_between';//时间间隔
    const SEARCH_KEYWORD_TYPE_MORE = 'more';//大于
    const SEARCH_KEYWORD_TYPE_LESS = 'less';//小于

    /**
     * @param $searchKeywords
     * @param $query
     * @param array $rules
     * @param Closure|null $customQuery
     * @return Builder
     */

    public static function query($searchKeywords, $query, array $rules, Closure $customQuery = null)
    {
        /**
         * @var Builder $query
         */
        return $query->where(function (Builder $subQuery) use ($searchKeywords, $rules, $customQuery) {
            foreach ($rules as $rule) {
                logs()->info('action rule', $rule);
                $subQuery = SearchKeyword::getQueryByRuleFromRequest($searchKeywords, $rule, $subQuery);
            }
            logs()->info('search query sql:', ['sql' => $subQuery->toSql()]);
            if ($customQuery === null) {
                return $subQuery;
            }
            return $customQuery($subQuery);
        });
    }

    /**
     * 根据规则调用对应的方法处理搜索条件
     *
     * @param $searchKeywords
     * @param $rule
     * @param Builder $subQuery
     * @return mixed
     */

    public static function getQueryByRuleFromRequest($searchKeywords, $rule, Builder $subQuery)
    {
        if (!isset($rule['type'])) {
            throw new InvalidArgumentException('search keyword rule type required');
        }
        $ruleType = $rule['type'] instanceof Closure ? $rule['type']($searchKeywords) : $rule['type'];
        if ($ruleType === null) {
            return $subQuery;
        }
        if (isset($rule['required']) && !empty($rule['required']) && self::checkValueIssetAndEmpty($searchKeywords, $rule['required']) === null) {
            return $subQuery;
        }
        $method = 'getQueryBy' . studly_case($ruleType) . 'Rule';
        //
        if (self::isSpecialType($ruleType)) {
            return self::$method($searchKeywords, $rule, $subQuery);
        }
        //
        if (isset($rule['value']) && is_array($rule['key'])) {
            return $subQuery;
        }
        //
        if (isset($rule['value']) && isset($rule['key'])) {
            logs()->info('fixed value rule', ['rule' => $rule]);
            $value = $rule['value'] instanceof Closure ? $rule['value']($searchKeywords, $rule) : $rule['value'];
            logs()->info('fixed value result', ['value' => $value]);
            return $value === null ? $subQuery : self::$method(self::convertQueryKey($rule['key'], $rule), $value, $subQuery);
        }
        //
        list($readKey, $queryKey) = self::checkValue($searchKeywords, self::convertRuleKeyToKey($rule));
        logs()->info('key info', ['readKey' => $readKey, 'queryKey' => $queryKey]);
        if ($readKey === null || $queryKey === null) {
            return $subQuery;
        }
        //
        logs()->info('begin filter value');
        $value = self::filterValue($searchKeywords, $readKey, $rule);
        if ($value === null) {
            return $subQuery;
        }
        //
        $value = self::convertValue($searchKeywords, $rule, $readKey, $value);
        logs()->info('value', ['value' => $value]);
        //
        return empty($value) && $value !== 0 ? $subQuery : self::$method(self::convertQueryKey($queryKey, $rule), $value, $subQuery);
    }

    /**
     * @param $queryKey
     * @param $rule
     * @return string
     */

    protected static function convertQueryKey($queryKey, $rule)
    {
        return isset($rule['table']) && !empty($rule['table']) ? $rule['table'] . '.' . $queryKey : $queryKey;
    }

    /**
     * @param $type
     * @return bool
     */

    protected static function isSpecialType($type)
    {
        $specialType = [self::SEARCH_KEYWORD_TYPE_BETWEEN, self::SEARCH_KEYWORD_TYPE_DATE_BETWEEN];
        return in_array($type, $specialType);
    }

    /**
     * 转换规则的key为实际的key
     *
     * @param $rule
     * @return array|null
     */

    protected static function convertRuleKeyToKey($rule)
    {
        if (!isset($rule['key'])) {
            throw new InvalidArgumentException('search keyword rule key required');
        }
        $arrayKey = is_string($rule['key']) ? [$rule['key']] : (is_array($rule['key']) ? $rule['key'] : null);
        if ($arrayKey === null) {
            throw new InvalidArgumentException('search keyword rule key invalid');
        }
        return $arrayKey;
    }

    /**
     * 检查值是否符合规范
     *
     * @param $searchKeywords
     * @param $arrayKey
     * @return array
     */

    protected static function checkValue($searchKeywords, $arrayKey)
    {
        if (count($arrayKey) > 1) {
            $arrayKey = collect($arrayKey)->filter(function ($value, $key) use ($searchKeywords) {
                $readKey = is_numeric($key) ? $value : $key;
                return isset($searchKeywords[$readKey]) && !empty($searchKeywords[$readKey]);
            })->values()->toArray();
        }
        logs()->info('array key info', $arrayKey);
        if (count($arrayKey) === 1 && isset($arrayKey[0])) {
            return [$arrayKey[0], $arrayKey[0]];
        } else if (count($arrayKey) === 1 && !isset($arrayKey[0])) {
            $key = array_keys($arrayKey)[0];
            return [$key, $arrayKey[$key]];
        } else {
            return [null, null];
        }
    }

    /**
     * 检查区间值是否符合规范
     *
     * @param $searchKeyword
     * @param $key
     * @return array
     */

    protected static function checkBetweenValue($searchKeyword, $key)
    {
        if (self::checkValueIssetAndEmpty($searchKeyword, $key) === null) {
            return [null, null];
        }
        list($beginValue, $endValue) = $searchKeyword[$key];
        if (!empty($beginValue) && !empty($endValue)) {
            return [$beginValue, $endValue];
        }
        return [null, null];
    }

    /**
     * 过滤之后的值
     *
     * @param $searchKeywords
     * @param $filterKey
     * @param $rule
     * @return null
     */

    protected static function filterValue($searchKeywords, $filterKey, $rule)
    {
        if (self::checkValueIssetAndEmpty($searchKeywords, $filterKey) === null) {
            return null;
        }
        if (!isset($rule['filter']) || empty($rule['filter'])) {
            return isset($searchKeywords[$filterKey]) ? self::checkValueEmpty($searchKeywords[$filterKey]) : null;
        }
        if (in_array($rule['type'], [
                self::SEARCH_KEYWORD_TYPE_IN,
                self::SEARCH_KEYWORD_TYPE_DATE_BETWEEN,
                self::SEARCH_KEYWORD_TYPE_BETWEEN
            ]) && is_array($searchKeywords[$filterKey])) {
            $filterNewKey = $filterKey . '.*';
        } else {
            $filterNewKey = $filterKey;
        }
        logs()->info('filter new key:' . $filterNewKey);
        if (isset($rule['filter'][$filterKey])) {
            $rules = [$filterNewKey => $rule['filter'][$filterKey]];
        } else {
            $rules = [$filterNewKey => $rule['filter']];
        }
        logs()->info('last rules', $rules);
        $validator = Validator::make($searchKeywords, $rules);
        logs()->info('filter error', $validator->errors()->toArray());
        if ($validator->errors()->isEmpty()) {
            return self::checkValueEmpty($searchKeywords[$filterKey]);
        }
        return null;
    }

    /**
     * 检测值是否为空
     *
     * @param $value
     * @return null
     */

    protected static function checkValueEmpty($value)
    {
        return (empty($value) && $value !== 0) ? null : $value;
    }

    /**
     * @param $dataList
     * @param $key
     * @return bool|null
     */

    public static function checkValueIssetAndEmpty($dataList, $key)
    {
        return isset($dataList[$key]) ? (empty($dataList[$key]) ? null : true) : null;
    }

    /**
     * @param $searchKeywords
     * @param $rule
     * @param $key
     * @param $value
     * @return mixed
     */

    protected static function convertValue($searchKeywords, $rule, $key, $value)
    {
        if (!isset($rule['convert']) || empty($rule['convert'])) {
            return $value;
        }
        if (isset($rule['convert'][$key]) && $rule['convert'][$key] instanceof Closure) {
            return $rule['convert'][$key]($value, $key, $searchKeywords);
        }
        return $value;
    }

    /**
     * 模糊搜索
     *
     * @param $queryKey
     * @param $value
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByLikeRule($queryKey, $value, $subQuery)
    {
        return $subQuery->where($queryKey, 'like', '%' . $value . '%');
    }

    /**
     * 精准匹配
     *
     * @param $queryKey
     * @param $value
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByMatchRule($queryKey, $value, $subQuery)
    {
        return $subQuery->where($queryKey, '=', $value);
    }

    /**
     * 存在多个条件
     *
     * @param $queryKey
     * @param $value
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByInRule($queryKey, $value, $subQuery)
    {
        logs()->info('query by in rule', ['queryKey' => $queryKey, 'value' => $value]);
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value)) {
            return $subQuery;
        }
        return $subQuery->whereIn($queryKey, $value);
    }


    /**
     * 大于条件
     *
     * @param $queryKey
     * @param $value
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByMoreRule($queryKey, $value, $subQuery)
    {
        return $subQuery->where($queryKey, '>', $value);
    }

    /**
     * 小于条件
     *
     * @param $queryKey
     * @param $value
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByLessRule($queryKey, $value, $subQuery)
    {
        return $subQuery->where($queryKey, '<', $value);
    }

    /**
     * @param $searchKeywords
     * @param $rule
     * @return array|null
     */

    protected static function getBetweenValue($searchKeywords, $rule)
    {
        $keys = self::convertRuleKeyToKey($rule);//找出搜索关键词
        logs()->info('between rule keys info', $keys);
        if (isset($rule['value'])) {
            $value = $rule['value'] instanceof Closure ? $rule['value']($searchKeywords, $rule) : $rule['value'];
            if ($value === null || !is_array($value) || count($value) !== 2) {
                return null;
            }
            list($beginValue, $endValue) = $value;
            list($readKey, $queryKey) = self::checkValue($searchKeywords, is_numeric(array_keys($keys)[0]) ? array_values($keys) : $keys);
            return [self::convertQueryKey($queryKey, $rule), $beginValue, $endValue];
        }
        $filterKeys = collect($keys)->filter(function ($value, $key) use ($searchKeywords) {
            list($beginValue, $endValue) = self::checkBetweenValue($searchKeywords, is_numeric($key) ? $value : $key);
            return !empty($beginValue) && !empty($endValue);
        });//找出搜索内容不为空的搜索条件
        logs()->info('filter between keys', $filterKeys->toArray());
        if ($filterKeys->count() !== 1) {
            return null;
        }
        logs()->info('filter between keys', $filterKeys->toArray());
        //如果数组key为数字，只传数组的value
        list($readKey, $queryKey) = self::checkValue($searchKeywords, is_numeric(array_keys($filterKeys->toArray())[0]) ? array_values($filterKeys->toArray()) : $filterKeys->toArray());
        list($beginValue, $endValue) = self::filterValue($searchKeywords, $readKey, $rule);
        logs()->info('between keys', ['queryKey' => $queryKey, 'beginValue' => $beginValue, 'endValue' => $endValue]);
        if ($beginValue === null || $endValue === null) {
            return null;
        }
        $beginValue = self::convertValue($searchKeywords, $rule, $readKey, $beginValue);
        $endValue = self::convertValue($searchKeywords, $rule, $readKey, $endValue);
        return [self::convertQueryKey($queryKey, $rule), $beginValue, $endValue];
    }

    /**
     * 区间条件
     *
     * @param $searchKeywords
     * @param $rule
     * @param Builder $subQuery
     * @return $this|Builder
     */

    protected static function getQueryByBetweenRule($searchKeywords, $rule, Builder $subQuery)
    {
        if (!$betweenValue = self::getBetweenValue($searchKeywords, $rule)) {
            return $subQuery;
        }
        logs()->info('between rule apply');
        list($queryKey, $beginValue, $endValue) = $betweenValue;
        return $subQuery->whereBetween($queryKey, [$beginValue, $endValue]);

    }

    /**
     * 时间区间条件
     *
     * @param $searchKeywords
     * @param $rule
     * @param Builder $subQuery
     * @return $this|Builder
     * @throws \Exception
     */

    protected static function getQueryByDateBetweenRule($searchKeywords, $rule, Builder $subQuery)
    {
        $rule['filter'] = isset($rule['filter']) ? $rule['filter'] : 'date_format:Y-m-d';
        if (!$betweenValue = self::getBetweenValue($searchKeywords, $rule)) {
            return $subQuery;
        }
        logs()->info('date between rule apply');
        list($queryKey, $beginValue, $endValue) = $betweenValue;
        return $subQuery->where($queryKey, '>=', (new Carbon($beginValue))->setTime(0, 0))
            ->where($queryKey, '<=', (new Carbon($endValue))->setTime(23, 59, 59));
    }

}
