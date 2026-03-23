<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_recovery_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('last_cart_activity_at');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->string('recovered_reason')->nullable();
            $table->json('cart_snapshot');
            $table->timestamps();

            $table->index(['status', 'last_cart_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_recovery_reminders');
    }
};
