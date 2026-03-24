<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 32)->nullable()->unique()->after('role');
            $table->foreignId('referred_by_user_id')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
            $table->decimal('referral_credit_balance', 10, 2)->default(0)->after('referred_by_user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_card_id')->nullable()->after('promo_code_id')
                ->constrained('gift_cards')->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->after('gift_card_id')
                ->constrained('referrals')->nullOnDelete();
            $table->decimal('gift_card_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('referral_credit_amount', 10, 2)->default(0)->after('gift_card_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referral_id');
            $table->dropConstrainedForeignId('gift_card_id');
            $table->dropColumn(['gift_card_amount', 'referral_credit_amount']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn(['referral_code', 'referral_credit_balance']);
        });
    }
};
