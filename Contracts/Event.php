<?php

namespace ZhiEq\Contracts;

use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use SerializesModels;
}
