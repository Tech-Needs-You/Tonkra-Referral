<?php

use Tonkra\Referral\Http\Controllers\ReferralInvoiceController;
use Tonkra\Referral\Http\Controllers\ReferralRegisterController;
use Tonkra\Referral\Http\Controllers\ReferralController;
use Tonkra\Referral\Http\Controllers\ReferralPaymentController;

Route::middleware(['web'])->name('referral.')->group(function () {

	Route::get('/referral/register', [ReferralRegisterController::class, 'show'])->name('register');
	Route::post('/referral/register', [ReferralRegisterController::class, 'register'])->name('register.post');


	Route::get('/referral/register/{referrer}', [ReferralRegisterController::class, 'show'])->name('register.with_referrer');
	Route::post('/referral/register/{referrer}', [ReferralRegisterController::class, 'register'])->name('register.with_referrer.post');

	Route::get('/referrals', [ReferralController::class, 'index'])->name('index');

	Route::get('user-avatar/{avatar}', [ReferralController::class, 'user_avatar'])->name('user.user_avatar');

	Route::get('referrer-avatar', [ReferralCOntroller::class, 'referrer_avatar'])->name('user.referrer_avatar');



	// ====================================================================================================
	// Customer Routes
	// ====================================================================================================
	Route::prefix('referral')->name('customer.')->group(function () {
		Route::get('/', [ReferralController::class, 'index'])->name('index');

		Route::post('downliners/search', [ReferralController::class, 'downliners'])->name('downliners.search');
		Route::post('downliners/search/{user}', [ReferralController::class, 'downliners'])->name('user_downliners.search');
		Route::post('preferences/{key}', [ReferralController::class, 'savePreference'])->name('preferences');

		Route::any('callback/paystack', [ReferralPaymentController::class, 'paystack'])->name('callback.paystack');

		Route::post('bonus/redeem', [ReferralController::class, 'redeemBonus'])->name('bonus.redeem');
		Route::post('bonus/withdraw', [ReferralController::class, 'withdrawBonus'])->name('bonus.withdraw');
		Route::post('bonus/transfer', [ReferralController::class, 'transferBonus'])->name('bonus.transfer');
		Route::post('bonus/bulk-transfer', [ReferralController::class, 'BulkTransferBonus'])->name('bonus.bulk_transfer');

		Route::post('redemptions/search', [ReferralController::class, 'redemptions'])->name('redemptions.search');
		Route::post('earnings/search', [ReferralController::class, 'earnings'])->name('earnings.search');
	});



	// ====================================================================================================
	// Admin Routes
	// ====================================================================================================
	Route::prefix(config('referral.admin_path'))->name('admin.')->group(function () {
		Route::get('/referral', [ReferralController::class, 'showAdminReferralSettings'])->name('setting');
		Route::post('/referral', [ReferralController::class, 'storeAdminReferralSettings'])->name('setting.store');
		Route::get('invoice', [ReferralInvoiceController::class, 'index'])->name('invoices');
		Route::get('invoice/{invoice}/approve', [ReferralInvoiceController::class, 'approve'])->name('invoices.approve');
		Route::post('redemptions/search', [ReferralController::class, 'searchAdminRedemptions'])->name('redemptions.search');
		Route::post('referral/search', [ReferralController::class, 'searchAdminReferrals'])->name('referrals.search');
		Route::get('redemptions/{redemption}/show', [ReferralInvoiceController::class, 'showAdminRedemption'])->name('redemptions.show');
		Route::put('redemptions/{redemption}/update-status', [ReferralController::class, 'updateRedemptionStatus'])->name('redemptions.update-status');
	});
});
