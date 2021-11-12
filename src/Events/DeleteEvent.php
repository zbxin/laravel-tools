<?php

namespace Zbxin\Events;

abstract class DeleteEvent extends Event
{
    use EventQueryModelTrait;

    /**
     * DeleteEvent constructor.
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
        return '删除成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '删除失败';
    }
}
