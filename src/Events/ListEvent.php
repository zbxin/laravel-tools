<?php

namespace ZhiEq\Events;

use Symfony\Component\HttpFoundation\HeaderBag;

abstract class ListEvent extends Event
{
    /**
     * @var HeaderBag
     */

    public $headers;

    /**
     * ListEvent constructor.
     * @param array $headers
     */

    public function __construct($headers)
    {
        $this->headers = new HeaderBag($headers);
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
