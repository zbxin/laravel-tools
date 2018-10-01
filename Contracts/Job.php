<?php

namespace ZhiEq\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Monolog\Handler\BufferHandler;
use Monolog\Logger;

abstract class Job implements ShouldQueue
{

    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels;

    abstract public function handle();

    public function writeLog()
    {
        $logger = app('log');
        if (env('APP_ENV') === 'testing') {
            return;
        }
        $handlers = $logger instanceof Logger ? $logger->getHandlers() : $logger->getMonolog()->getHandlers();
        foreach ($handlers as $handler) {
            $handler instanceof BufferHandler && $handler->close();
        }
    }

    public function __destruct()
    {
        //$this->writeLog();
    }
}
