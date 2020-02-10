<?php

namespace ZhiEq\Events;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait DefinitionEventController
{

  /**
   * @param Request $request
   * @return JsonResponse
   */

  public function getDefinitionList(Request $request)
  {
    return EventController::triggerEventWithTransaction(EventController::newEventClass($this->baseEventPath(), $this->eventNamespace(), 'DefinitionQuery', $request->header()));
  }
}
