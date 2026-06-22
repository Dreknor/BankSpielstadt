<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $von = config('bank.aktien.fixing_von', '06:00');
        $bis = config('bank.aktien.fixing_bis', '16:00');

        //$schedule->command('aktien:kurs-berechnen')->hourly();
        $schedule->command('aktien:kurs-berechnen')
            ->weekdays()
            ->everyThirtyMinutes()
            ->between($von, $bis);
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
