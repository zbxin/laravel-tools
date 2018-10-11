<?php

namespace ZhiEq\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CronTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Cron Task Listen';

    /**
     * 定时任务列表
     *
     * @var array
     */

    protected $tasks = [
        ['command' => 'schedule:run', 'interval' => 60, 'align' => 0]
    ];

    /**
     * @var array
     */

    protected $nextTasks = [];

    /**
     * @var
     */

    protected $nextRunTime;

    /**
     * CronTask constructor.
     */

    public function __construct()
    {
        parent::__construct();
        $this->tasks = array_merge($this->tasks, config('tools.cron_tasks'));
        $this->tasks = array_map(function ($task) {
            $nextRun = Carbon::now()->addSeconds($task['interval']);
            $task['nextRunTime'] = $task['align'] === false ?
                $nextRun->timestamp :
                (new Carbon($nextRun->format('Y-m-d H:i:' . $task['align'])))->timestamp;
            return $task;
        }, $this->tasks);
    }

    /**
     * @return void
     */

    protected function nextRunTime()
    {
        $this->nextRunTime = collect($this->tasks)->sortBy('nextRunTime')->first()['nextRunTime'];
    }

    /**
     * @return string
     */

    protected function nowTime()
    {
        return Carbon::now()->format('Y-m-d H:i:s.u') . '/' . microtime(true);
    }

    /**
     * 计算下次需要运行的任务
     */

    protected function calculateNextTask()
    {
        $this->nextTasks = [];
        $this->tasks = array_map(function ($task) {
            if ($task['nextRunTime'] === $this->nextRunTime) {
                $this->nextTasks[] = $task;
                $task['nextRunTime'] += $task['interval'];
            }
            return $task;
        }, $this->tasks);
    }


    public function handle()
    {
        $this->recordInfo('Begin Run Cron Listen At:' . $this->nowTime());
        while (true) {
            $this->nextRunTime();
            $this->calculateNextTask();
            if ($this->nextRunTime > time()) {
                $this->recordInfo('Cron Will Start At:' . date('Y-m-d H:i:s', $this->nextRunTime) . '/' . $this->nextRunTime, 'warn');
                time_sleep_until($this->nextRunTime);
            }
            $this->recordInfo('Start Cron At:' . $this->nowTime());
            foreach ($this->nextTasks as $task) {
                $this->recordInfo('Run Task:' . $task['command'] . ',At:' . $this->nowTime());
                $this->call($task['command']);
            }
            $this->recordInfo('Finish Cron At:' . $this->nowTime());
        }
    }

    /**
     * @param $message
     * @param string $type
     */

    protected function recordInfo($message, $type = 'info')
    {
        logs()->info($message);
        $this->{$type}($message);
    }

}
