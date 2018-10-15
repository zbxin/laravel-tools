<?php

namespace ZhiEq\Utils;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class Trigger
{
    /**
     * @param $event
     * @return bool|mixed
     */

    public static function eventResult($event)
    {
        $eventResult = event($event);
        if (count(Event::getListeners(get_class($event))) !== count($eventResult)) {
            return false;
        }
        $results = array_filter($eventResult, function ($result) {
            return $result !== true && $result !== null;
        });
        return empty($results) ? true : array_pop($results);
    }

    /**
     * @param $event
     * @return mixed
     */

    public static function eventWithTransaction($event)
    {
        return DB::transaction(function (Connection $db) use ($event) {
            $result = self::eventResult($event);
            if ($result === false) {
                $db->rollBack();
            }
            return $result;
        });
    }
}