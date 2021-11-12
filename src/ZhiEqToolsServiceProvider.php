<?php

namespace Zbxin;

use Illuminate\Support\ServiceProvider;
use Zbxin\Commands\CronTask;

class ZhiEqToolsServiceProvider extends ServiceProvider
{
    /**
     * 工具包命令行列表
     *
     * @var array
     */

    protected $commands = [
        CronTask::class,
    ];

    /**
     * @return string
     */

    protected function configPath()
    {
        return __DIR__ . '/../config/tools.php';
    }

    /**
     *
     */

    public function boot()
    {
        //推送配置文件
        $this->publishes([
            $this->configPath() => config_path('tools.php'),
        ]);
        //注册命令行
        $this->app->runningInConsole() && $this->commands($this->commands);
    }

    /**
     *
     */

    public function register()
    {
        //合并配置文件信息
        $this->mergeConfigFrom($this->configPath(), 'tools');
    }
}
