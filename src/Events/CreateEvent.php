<?php

namespace ZhiEq\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

abstract class CreateEvent extends ValidatorEvent
{
  /**
   * @var Model
   */

  public $newModel;

  /**
   * CreateEvent constructor.
   * @param $input
   * @throws ValidationException
   */

  public function __construct($input)
  {
    $this->validateInput($input);
    $modelClass = $this->modelClass();
    $this->newModel = new $modelClass();
  }

  /**
   * @return string
   */

  abstract protected function modelClass();

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
