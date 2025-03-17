<?php

use Tonkra\Referral\Http\Controllers\ReferralRegisterController;

Route::middleware(['web'])->group(function () {
	Route::get('/referral/register', [ReferralRegisterController::class, 'show'])->name('referral.register');
	Route::post('/referral/register', [ReferralRegisterController::class, 'register'])->name('referral.register.post');


	Route::get('/referral/register/{referrer}', [ReferralRegisterController::class, 'show'])->name('referral.register.with_referrer');
	Route::post('/referral/register/{referrer}', [ReferralRegisterController::class, 'register'])->name('referral.register.with_referrer.post');
});
