<?php

namespace ZhiEq\Events;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait CurdEventController
{

  /**
   * @param Request $request
   * @return JsonResponse
   */

  public function getList(Request $request)
  {
    return EventController::triggerEvent(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'ListQuery', $request->header()));
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */

  public function postInfo(Request $request)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Create', $request->input()));
  }

  /**
   * @param $code
   * @return JsonResponse
   */

  public function getInfo($code)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Query', $code));
  }

  /**
   * @param $code
   * @param Request $request
   * @return JsonResponse
   */

  public function putInfo($code, Request $request)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Update', $code, $request->input()));
  }

  /**
   * @param $code
   * @return JsonResponse
   */

  public function deleteInfo($code)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'Delete', $code));
  }
}
