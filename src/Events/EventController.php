<?php

namespace ZhiEq\Events;

use Illuminate\Http\JsonResponse;
use ZhiEq\Exceptions\CustomException;
use ZhiEq\Utils\Trigger;

class EventController
{

  /**
   * @param $baseEventPath
   * @param $eventNamespace
   * @param $class
   * @param mixed ...$params
   * @return Event
   */

  public static function newEventClass($baseEventPath, $eventNamespace, $class, ...$params)
  {
    $eventName = $baseEventPath . $eventNamespace . '\\' . $class;
    $event = new $eventName(...$params);
    if (!$event instanceof Event) {
      throw new CustomException('Event Must Extend App\\Extend\\Event Class.');
    }
    return $event;
  }

  /**
   * @param Event $event
   * @return JsonResponse
   */

  public static function triggerEventWithTransaction($event)
  {
    $result = Trigger::eventWithTransaction($event);
    if ($result === false) {
      return errors($event->failedMessage());
    }
    return success($result === true ? [] : $result, $event->successMessage());
  }

  /**
   * @param Event $event
   * @return JsonResponse
   */

  public static function triggerEvent($event)
  {
    $result = Trigger::eventResult($event);
    if ($result === false) {
      return errors($event->failedMessage());
    }
    return success($result === true ? [] : $result, $event->successMessage());
  }
}
