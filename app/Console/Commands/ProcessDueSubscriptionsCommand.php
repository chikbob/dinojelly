<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessDueSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process-due {--limit=25 : Max number of subscriptions to process}';

    protected $description = 'Create orders for active subscriptions that reached next_run_at';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $processed = $subscriptionService->processDueSubscriptions((int) $this->option('limit'));

        $this->info("Processed {$processed} subscriptions.");

        return self::SUCCESS;
    }
}
