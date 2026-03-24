<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('message')->nullable();
            $table->foreignId('purchaser_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('initial_amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('currency', 3)->default('RUB');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['recipient_user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
