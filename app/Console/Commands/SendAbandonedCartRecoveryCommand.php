<?php

namespace App\Console\Commands;

use App\Services\AbandonedCartService;
use Illuminate\Console\Command;

class SendAbandonedCartRecoveryCommand extends Command
{
    protected $signature = 'cart:send-recovery-reminders {--minutes=60 : Inactivity threshold in minutes}';

    protected $description = 'Queue abandoned cart recovery reminders for stale carts';

    public function handle(AbandonedCartService $abandonedCartService): int
    {
        $scheduled = $abandonedCartService->scheduleReminders((int) $this->option('minutes'));

        $this->info("Queued {$scheduled} abandoned cart reminders.");

        return self::SUCCESS;
    }
}
