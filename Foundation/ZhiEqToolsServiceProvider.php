<?php

namespace ZhiEq;

use Illuminate\Support\ServiceProvider;

class ZhiEqToolsServiceProvider extends ServiceProvider
{

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
