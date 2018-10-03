<?php

namespace ZhiEq\Utils;

class ApiToken
{
    const HEADER_ACCESS_TOKEN_KEY = 'X-Access-Token';

    protected static $_cacheKey = 'access-token';

    protected static function getTokenExpired()
    {
        return 120;
    }

    protected static function cacheKey()
    {
        if (static::$_cacheKey == null) {
            throw new \InvalidArgumentException('must be set cacheKey');
        }
        return static::$_cacheKey;
    }

    public static function generate($userId)
    {
        $token = str_random(40);
        try {
            if (cache()->tags(static::cacheKey())->add($token, $userId, static::getTokenExpired())) {
                return $token;
            }
            return null;
        } catch (\Exception $exception) {
            logs()->error($exception);
            return null;
        }

    }

    public static function refresh($token)
    {
        try {
            cache()->tags(static::$_cacheKey)->put($token, static::get($token), static::getTokenExpired());
        } catch (\Exception $exception) {
            logs()->error($exception);
        }
    }

    public static function clear($token)
    {
        try {
            cache()->tags(static::$_cacheKey)->forget($token);
        } catch (\Exception $exception) {
            logs()->error($exception);
        }
    }

    public static function get($token)
    {
        try {
            return cache()->tags(static::$_cacheKey)->get($token);
        } catch (\Exception $exception) {
            logs()->error($exception);
            return null;
        }

    }

    public static function getAndRefresh($token)
    {
        $userId = static::get($token);
        $userId !== null && static::refresh($token);
        return $userId;
    }
}
