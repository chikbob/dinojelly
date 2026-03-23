<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('last_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('name');
            $table->enum('payment_method', ['card', 'cash'])->default('card');
            $table->string('status')->default('active');
            $table->unsignedInteger('interval_days')->default(30);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['subscription_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('subscription_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_id');
        });

        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
    }
};
