<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('code', 32);
            $table->string('status', 32)->default('pending');
            $table->string('referred_email')->nullable();
            $table->decimal('reward_amount', 10, 2)->default(300);
            $table->timestamp('rewarded_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['referrer_user_id', 'referred_user_id']);
            $table->index(['code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
