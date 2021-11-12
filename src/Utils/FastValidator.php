<?php

namespace Zbxin\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class FastValidator
{
  use ValidatesAttributes;

  /**
   * @var array
   */

  protected $message;

  /**
   * @var array
   */

  protected $dataTable;

  /**
   * @var array
   */

  protected $rules;

  /**
   * @var array
   */

  protected $data;

  /**
   * FastValidator constructor.
   * @param array $dataTable
   * @param array $rules
   */

  public function __construct(array &$dataTable, array $rules)
  {
    $this->dataTable = $dataTable;
    $this->resolveRules($rules);
  }

  /**
   * @param array $dataTable
   * @param array $rules
   * @return FastValidator
   */

  public static function make(array &$dataTable, array $rules)
  {
    return new self($dataTable, $rules);
  }

  /**
   * @param array $rules
   */

  protected function resolveRules(array $rules)
  {
    foreach ($rules as $attribute => $ruleGroup) {
      if (is_string($ruleGroup)) {
        $ruleGroup = explode('|', $ruleGroup);
      }
      if (!is_array($ruleGroup)) {
        continue;
      }
      $nullAble = false;
      if (($pos = array_search('nullable', $ruleGroup)) !== false) {
        unset($ruleGroup[$pos]);
        $nullAble = true;
      }
      foreach ($ruleGroup as $rule) {
        list($method, $parameters) = (strpos($rule, ':') === false ? [$rule, ''] : explode(':', $rule));
        $this->rules[] = [
          'attribute' => $attribute,
          'method' => $method,
          'parameters' => explode(',', $parameters),
          'message' => $attribute . ' ' . Str::title($method),
          'nullAble' => $nullAble
        ];
        logs()->info('rule explode', [
          'rule' => $rule,
          'method' => $method,
          'parameters' => $parameters,
        ]);
      }
    }
  }

  /**
   * @return MessageBag
   * @throws \Exception
   */

  public function errors()
  {
    if ($this->message === null) {
      $this->message = new MessageBag();
      foreach ($this->dataTable as $row => $item) {
        $this->data = $item;
        logs()->info('run one row start', ['data' => $item]);
        foreach ($this->rules as $rule) {
          $method = 'validate' . studly_case($rule['method']);
          if (!method_exists($this, $method)) {
            throw new \Exception('fast validator {' . $method . '} method not found');
          }
          if ($rule['nullAble'] === true) {
            if (!empty($item[$rule['attribute']]) && !$this->{$method}($rule['attribute'], $item[$rule['attribute']], $rule['parameters'])) {
              $this->message->add($row . '.' . $rule['attribute'], $rule['message']);
            }
            continue;
          }
          if (!$this->{$method}($rule['attribute'], $item[$rule['attribute']], $rule['parameters'])) {
            $this->message->add($row . '.' . $rule['attribute'], $rule['message']);
          }
        }
        logs()->info('run one row end');
      }
    }
    return $this->message;
  }

  /**
   * @param $attribute
   * @param $value
   * @param $parameters
   * @return bool
   */

  public function validateExistsModel($attribute, $value, $parameters)
  {
    if (empty($parameters[0])) {
      return false;
    }
    /**
     * @var Model|Builder $model
     */
    $model = $parameters[0];
    if (isset($parameters[2]) && $parameters[2] instanceof \Closure) {
      return $model::query()->where($parameters[2])->where($parameters[1], $value)->exists();
    }
    return $model::query()->where($parameters[1], $value)->exists();
  }
}
