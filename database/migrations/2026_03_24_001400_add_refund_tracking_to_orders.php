<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('gift_card_refunded_at')->nullable()->after('referral_credit_amount');
            $table->timestamp('referral_credit_refunded_at')->nullable()->after('gift_card_refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['gift_card_refunded_at', 'referral_credit_refunded_at']);
        });
    }
};
