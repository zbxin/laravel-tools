<?php

namespace Zbxin\Events;

use Illuminate\Validation\ValidationException;

abstract class UpdateEvent extends ValidatorEvent
{
  use EventQueryModelTrait;

  /**
   * UpdateEvent constructor.
   * @param $code
   * @param array $input
   * @throws ValidationException
   */

  public function __construct($code, array $input)
  {
    $this->query($code);
    $this->validateInput($input);
  }

  /**
   * @return string
   */

  public function successMessage()
  {
    return '保存成功';
  }

  /**
   * @return string
   */

  public function failedMessage()
  {
    return '保存失败';
  }
}
