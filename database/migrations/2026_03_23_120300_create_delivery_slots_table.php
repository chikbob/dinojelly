<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('capacity')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('address_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('delivery_slot_id')
                ->nullable()
                ->after('address_id')
                ->constrained()
                ->nullOnDelete();
            $table->decimal('delivery_price', 10, 2)->default(0)->after('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('address_id');
            $table->dropConstrainedForeignId('delivery_slot_id');
            $table->dropColumn('delivery_price');
        });

        Schema::dropIfExists('delivery_slots');
    }
};
