<?php

namespace ZhiEq;

use Illuminate\Support\ServiceProvider;

class ZhiEqToolsServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */

    protected $defer = true;

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
    }
}
