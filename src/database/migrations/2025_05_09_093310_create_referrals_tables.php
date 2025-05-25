<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tonkra\Referral\Models\ReferralBonus;
use Tonkra\Referral\Models\ReferralRedemption;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('user_id')->unique();
            $table->json('preferences');
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('referral_code')->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('referral_bonuses', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('transaction_id')
                ->constrained('subscription_transactions', 'id')
                ->onDelete('cascade'); // or 'set null' if appropriate
            $table->foreignId('from')->constrained('users')->onDelete('cascade');
            $table->foreignId('to')->constrained('users')->onDelete('cascade');
            $table->decimal('bonus', 10, 2);
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->enum('status', [ReferralBonus::STATUS_PENDING, ReferralBonus::STATUS_PAID, ReferralBonus::STATUS_REJECTED, ReferralBonus::STATUS_REDEEMED, ReferralBonus::STATUS_PARTLY_REDEEMED])->default(ReferralBonus::STATUS_PENDING);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['from', 'to', 'status']);
        });

        Schema::create('referral_redemptions', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('request_id')->comment('Use to track how many redemptions came from the same request');
            $table->unsignedBigInteger('referral_bonus_id');
            $table->decimal('amount', 10, 2);
            $table->enum('status', [ReferralRedemption::STATUS_PENDING, ReferralRedemption::STATUS_PROCESSING, ReferralRedemption::STATUS_COMPLETED, ReferralRedemption::STATUS_FAILED])->default(ReferralRedemption::STATUS_PENDING);
            $table->string('payout_method'); // 'bank_transfer', 'sms_credit', etc.
            $table->json('payout_details')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'request_id', 'referral_bonus_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_redemptions');
        Schema::dropIfExists('referral_bonuses');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('user_preferences');
    }
};
