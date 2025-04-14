<?php

use App\Console\Commands\AssignYearlyHolidays;
use App\Console\Commands\GorgiasStatistics;
use App\Console\Commands\SyncGorgiasUserIdForNewUsers;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-failed')->dailyAt('10:00');
Schedule::command(AssignYearlyHolidays::class)->dailyAt('10:00');

Schedule::command(SyncGorgiasUserIdForNewUsers::class)->hourly();

// Commands for statistics
Schedule::command(GorgiasStatistics::class)->dailyAt('09:30');
