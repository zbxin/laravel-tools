<?php

namespace ZhiEq\Events;

/**
 * Class QueryEvent
 * @package ZhiEq\Events
 */

abstract class QueryEvent extends Event
{
    use EventQueryModelTrait;

    /**
     * QueryEvent constructor.
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
        return '查询成功';
    }

    /**
     * @return string
     */
    public function failedMessage()
    {
        return '查询失败';
    }
}
