<?php

namespace App\Console\Commands;

use App\Services\TeacherPool;
use Illuminate\Console\Command;

class SubscriptionPool extends Command
{
    protected $signature = 'zirak:plan-pool {month? : YYYY-MM, default last month}';

    protected $description = 'Share last month\'s Zirak Plus money with teachers (safe to run again)';

    public function handle(TeacherPool $pool): int
    {
        $month = $this->argument('month') ?: now('Asia/Kabul')->subMonthNoOverflow()->format('Y-m');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Month must look like 2026-11.');

            return self::FAILURE;
        }
        $shares = $pool->compute($month);
        $this->info("Plan money for {$month} shared with ".count($shares).' teacher(s).');

        return self::SUCCESS;
    }
}
