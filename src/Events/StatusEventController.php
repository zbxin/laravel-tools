<?php

namespace Zbxin\Events;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait StatusEventController
{

  /**
   * @param $code
   * @param Request $request
   * @return JsonResponse
   */

  public function putDisabled($code, Request $request)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Disabled', $code, $request->input()));
  }

  /**
   * @param $code
   * @param Request $request
   * @return JsonResponse
   */

  public function putEnabled($code, Request $request)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Enabled', $code, $request->input()));
  }
}
