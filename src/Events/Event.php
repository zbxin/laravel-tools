<?php

namespace ZhiEq\Events;

abstract class Event
{

    /**
     * @return string
     */

    abstract public function successMessage();

    /**
     * @return string
     */

    abstract public function failedMessage();

}
