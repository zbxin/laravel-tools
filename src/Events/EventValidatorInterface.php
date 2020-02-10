<?php

namespace ZhiEq\Events;

interface EventValidatorInterface
{
    /**
     * @return array
     */

    public function rules();

    /**
     * @return array
     */

    public function messages();
}
