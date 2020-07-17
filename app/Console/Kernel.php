<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\SynchronizeExtSources;
use App\Jobs\SynchronizeLdap;
use App\Jobs\BuildIdentities;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new SynchronizeExtSources())->everyTenMinutes();
        $schedule->job(new SynchronizeLdap())->everyThirtyMinutes();
        $schedule->job(new BuildIdentities())->cron('0 0,3,6,9,12,15,18,21');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
