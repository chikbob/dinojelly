<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('source_order_id')
                ->nullable()
                ->after('last_order_id')
                ->constrained('orders')
                ->nullOnDelete();

            $table->index('source_order_id');
        });

        $subscriptions = DB::table('subscriptions')
            ->whereNull('source_order_id')
            ->whereNotNull('last_order_id')
            ->orderBy('last_order_id')
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'paused' THEN 1 WHEN 'canceled' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get(['id', 'last_order_id']);

        $assignedOrderIds = [];

        foreach ($subscriptions as $subscription) {
            if (isset($assignedOrderIds[$subscription->last_order_id])) {
                continue;
            }

            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['source_order_id' => $subscription->last_order_id]);

            $assignedOrderIds[$subscription->last_order_id] = true;
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_order_id');
        });
    }
};
