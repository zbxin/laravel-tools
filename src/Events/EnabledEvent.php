<?php

namespace ZhiEq\Events;

/**
 * Class EnabledEvent
 * @package App\Extend
 */
abstract class EnabledEvent extends Event
{
  use EventQueryModelTrait;

  /**
   * EnabledEvent constructor.
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
    return '启用成功';
  }

  /**
   * @return string
   */
  public function failedMessage()
  {
    return '启用失败';
  }
}
