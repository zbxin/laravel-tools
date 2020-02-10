<?php

namespace ZhiEq\Events;

use Illuminate\Validation\ValidationException;

abstract class StatusEvent extends ValidatorEvent
{
    use EventQueryModelTrait;

    /**
     * StatusEvent constructor.
     * @param $code
     * @param array $input
     * @throws ValidationException
     */

    public function __construct($code, array $input = [])
    {
        $this->query($code);
        $this->validateInput($input);
    }

    /**
     * @return string
     */

    public function successMessage()
    {
        return '修改状态成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '修改状态失败';
    }
}
