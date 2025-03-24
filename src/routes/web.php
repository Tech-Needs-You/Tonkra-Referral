<?php

use Tonkra\Referral\Http\Controllers\ReferralRegisterController;
use Tonkra\Referral\Http\Controllers\ReferralController;

Route::middleware(['web'])->name('referral.')->group(function () {

	Route::get('/referral/register', [ReferralRegisterController::class, 'show'])->name('register');
	Route::post('/referral/register', [ReferralRegisterController::class, 'register'])->name('register.post');


	Route::get('/referral/register/{referrer}', [ReferralRegisterController::class, 'show'])->name('register.with_referrer');
	Route::post('/referral/register/{referrer}', [ReferralRegisterController::class, 'register'])->name('register.with_referrer.post');

	Route::prefix('referrals')->name('customer.')->group(function () {
		Route::get('/', [ReferralController::class, 'index'])->name('index');

		Route::post('downliners/search', [ReferralController::class, 'downliners'])->name('downliners.search');
		Route::post('downliners/search/{user}', [ReferralController::class, 'downliners'])->name('user_downliners.search');
		Route::post('preferences/{key}', [ReferralController::class, 'savePreference'])->name('preferences');
	});

	Route::get('/referrals', [ReferralController::class, 'index'])->name('index');


	Route::get('user-avatar/{avatar}', [ReferralCOntroller::class, 'user_avatar'])->name('user.user_avatar');

	Route::get('referrer-avatar', [ReferralCOntroller::class, 'referrer_avatar'])->name('user.referrer_avatar');

	Route::prefix('admin')->name('admin.')->group(function () {
		Route::get('/referral', [ReferralController::class, 'showAdminReferralSettings'])->name('setting');
		Route::post('/referral', [ReferralController::class, 'storeAdminReferralSettings'])->name('setting.store');
	});
});
