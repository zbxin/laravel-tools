<?php

namespace ZhiEq\Events;

abstract class DisabledEvent extends Event
{
  use EventQueryModelTrait;

  /**
   * DisabledEvent constructor.
   * @param $code
   */

  public function __construct($code)
  {
    $this->query($code);
  }

  /**
   * @return string
   */
  public function successMessage()
  {
    return '禁用成功';
  }

  /**
   * @return string
   */
  public function failedMessage()
  {
    return '禁用失败';
  }
}
