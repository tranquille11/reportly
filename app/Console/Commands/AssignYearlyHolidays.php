<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AssignYearlyHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $currentYear = Carbon::now()->year;

        $a = $currentYear % 4;
        $b = $currentYear % 7;
        $c = $currentYear % 19;

        $d = (19 * $c + 15) % 30;
        $e = (2 * $a + 4 * $b - $d + 34) % 7;

        $easterMonth = ($d + $e + 114) / 31;
        $easterDay = (($d + $e + 114) % 31) + 1;

        $easterDate = Carbon::parse(jdtogregorian(juliantojd($easterMonth, $easterDay, $currentYear)))->format('Y-m-d');

        $holidays = Setting::where('key', 'holidays')->first()->value;

        $holidays['easter']['date'] = $easterDate;
        $holidays['christmas']['date'] = Carbon::createFromDate($currentYear, 12, 25)->format('Y-m-d');
        $holidays['new_years']['date'] = Carbon::createFromDate($currentYear, 1, 1)->format('Y-m-d');

        Setting::where('key', 'holidays')->update(['value' => $holidays]);

    }
}
