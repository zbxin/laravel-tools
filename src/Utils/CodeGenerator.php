<?php

namespace ZhiEq\Utils;

use Closure;
use Illuminate\Support\Facades\Redis;

/**
 * Class CodeGenerator
 * @package ZhiEq\Utils
 */

class CodeGenerator
{
    const TYPE_ONLY_NUMBER = 10;
    const TYPE_ONLY_LETTER = 20;
    const TYPE_NUMBER_AND_LETTER = 30;

    protected static $numberMap = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    protected static $letterMap = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    protected static function cacheTags($key)
    {
        return 'code-generator:' . $key;
    }

    /**
     * @param $type
     * @return array
     */

    protected static function getMapList($type)
    {
        if ($type === self::TYPE_ONLY_NUMBER) {
            return self::$numberMap;
        } elseif ($type === self::TYPE_ONLY_LETTER) {
            return self::$letterMap;
        } elseif ($type === self::TYPE_NUMBER_AND_LETTER) {
            return array_merge(self::$numberMap, self::$letterMap);
        } else {
            return [];
        }
    }

    /**
     * @param $max
     * @param $len
     * @param int $type
     * @param string $prefix
     * @param int $firstMin
     * @param string $firstMax
     * @return null|string
     */

    public static function getNext($max, $len, $type = self::TYPE_NUMBER_AND_LETTER, $prefix = '', $firstMin = 1, $firstMax = 'Z')
    {
        if ($len < 1 || !is_numeric($len)) {
            logs()->info('$len not numeric or less one');
            return null;
        }
        if (strlen($max) > $len) {
            logs()->info('$max code length more than to $len');
            return null;
        }
        $map = self::getMapList($type);
        logs()->info('map list', $map);
        $max = str_pad($max, $len, '0', STR_PAD_LEFT);
        logs()->info('padding $max to:' . $max);
        $first = substr($max, 0, 1);
        logs()->info('first letter is:' . $first);
        $firstPosition = self::getPosition($first, $map);
        $firstMinPosition = self::getPosition($firstMin, $map);
        $firstMaxPosition = self::getPosition($firstMax, $map);
        logs()->info('map position', ['firstPosition' => $firstPosition, 'firstMinPosition' => $firstMinPosition, 'firstMaxPosition' => $firstMaxPosition]);
        if ($firstPosition === false || $firstMinPosition === false || $firstMaxPosition === false) {
            logs()->info('position letter invalid');
            return null;
        }
        if ($firstPosition < $firstMinPosition) {
            $max = $firstMin . substr($max, 1, $len - 1);
        }
        logs()->info('first letter check $max:' . $max);
        if ($firstPosition > $firstMaxPosition) {
            return null;
        }
        $final = [];
        for ($i = 1; $i <= $len; $i++) {
            $result = self::getNextCharacter($map, $max, $i);
            logs()->info('next character:', ['next' => $result]);
            if ($result === false) {
                return null;
            }
            if ($result !== true) {
                $final[] = $result;
                break;
            }
            if ($result === true) {
                $final[] = $map[0];
            }
        }
        logs()->info('final str', $final);
        $diff = $len - count($final);
        logs()->info('diff:' . $diff);
        $finalStr = substr($max, 0, $diff) . implode('', array_reverse($final));
        logs()->info('finalStr:' . $finalStr);
        $firstPosition = self::getPosition(substr($finalStr, 0, 1), $map);
        logs()->info('check final str first position:' . $firstPosition);
        return $firstPosition > $firstMaxPosition ? null : $prefix . $finalStr;
    }

    /**
     * @param $map
     * @param $max
     * @param $reciprocal
     * @return bool
     */

    protected static function getNextCharacter($map, $max, $reciprocal)
    {
        $character = substr($max, -$reciprocal, 1);
        logs()->info('search letter:' . $character);
        $now = self::getPosition($character, $map);
        logs()->info('search result', ['now' => $now]);
        if ($now === false) {
            return false;
        }
        return $now === max(array_flip($map)) ? true : $map[$now + 1];
    }

    /**
     * @param $character
     * @param $map
     * @return false|int|string
     */

    protected static function getPosition($character, $map)
    {
        return array_search(strtoupper($character), $map, true);
    }

    /**
     * @return mixed
     */

    protected static function redis()
    {
        return Redis::connection()->client();
    }

    /**
     * @param $uniqueKey
     * @param Closure $maxCallback
     * @param $len
     * @param int $type
     * @param string $prefix
     * @param int $firstMin
     * @param string $firstMax
     * @return null|string
     */

    public static function getUniqueCode($uniqueKey, Closure $maxCallback, $len, $type = self::TYPE_NUMBER_AND_LETTER, $prefix = '', $firstMin = 1, $firstMax = 'Z')
    {
        $maxCode = self::redis()->incr(self::cacheTags($uniqueKey));
        if ($maxCode === 1) {
            $dbMax = self::convertCodeToInteger(value($maxCallback), $type);
            if ($dbMax > $maxCode) {
                self::redis()->set(self::cacheTags($uniqueKey), $dbMax);
                $maxCode = $dbMax;
            }
        }
        return self::getNext(self::convertIntegerToCode($maxCode, $type, $len), $len, $type, $prefix, $firstMin, $firstMax);
    }

    /**
     * @param $type
     * @return mixed|null
     */

    protected static function integerMap($type)
    {
        $map = [
            self::TYPE_ONLY_NUMBER => 10,
            self::TYPE_ONLY_LETTER => 26,
            self::TYPE_NUMBER_AND_LETTER => 36,
        ];
        return isset($map[$type]) ? $map[$type] : null;
    }

    /**
     * @param $code
     * @param $type
     * @return float|int|null
     */

    public static function convertCodeToInteger($code, $type)
    {
        if (!$coefficient = self::integerMap($type)) {
            return null;
        }
        $map = self::getMapList($type);
        $length = strlen($code);
        $return = 0;
        for ($i = 1; $i <= $length; $i++) {
            $singleCode = substr($code, -$i, 1);
            $base = pow($coefficient, $i - 1);
            $position = self::getPosition($singleCode, $map);
            $return += $base * $position;
        }
        return $return;
    }

    /**
     * @param $integer
     * @param $type
     * @param $length
     * @return bool|null|string
     */

    public static function convertIntegerToCode($integer, $type, $length)
    {
        if (!$coefficient = self::integerMap($type)) {
            return null;
        }
        $map = self::getMapList($type);
        $code = [];
        $hasConvert = 0;
        $i = 1;
        do {
            $remainder = ($integer % pow($coefficient, $i)) - $hasConvert;
            $offset = $remainder / pow($coefficient, $i - 1);
            $code[] = $map[$offset];
            $hasConvert += $remainder;
            $i++;
        } while ($i <= $length);
        return implode("", array_reverse($code));
    }
}
