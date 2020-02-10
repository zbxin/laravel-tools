<?php

namespace ZhiEq\Events;

use Illuminate\Validation\ValidationException;

abstract class UpdateEvent extends Event implements EventValidatorInterface
{
    use EventQueryModelTrait, EventValidatorTrait;

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
