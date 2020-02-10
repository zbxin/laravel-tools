<?php

namespace ZhiEq\Events;

interface EventControllerDefine
{
  /**
   * @return string
   */

  public function eventNamespace();

  /**
   * @return string
   */

  public function baseEventPath();
}
