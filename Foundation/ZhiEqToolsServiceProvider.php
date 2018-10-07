<?php

namespace ZhiEq;

use Illuminate\Support\ServiceProvider;
use ZhiEq\Commands\CronTask;

class ZhiEqToolsServiceProvider extends ServiceProvider
{
    protected $commands = [
        CronTask::class,
    ];

    /**
     * @return string
     */

    protected function configPath()
    {
        return __DIR__ . '/config/tools.php';
    }

    /**
     *
     */

    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('tools.php'),
        ]);
        $this->app->runningInConsole() && $this->commands($this->commands);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath(), 'tools'
        );
    }
}
