<?php

namespace Tonkra\Referral\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tonkra\Referral\Facades\ReferralSettings;

class ReferralRedirectMiddleware
{
	public function handle(Request $request, Closure $next)
	{
		// If referral system is enabled and user is accessing /register
		if (ReferralSettings::status() && $request->is('register')) {
			return redirect()->route('referral.register');
		}

		if (ReferralSettings::status() && $request->path() === 'callback/paystack') {
			return redirect()->route('referral.customer.callback.paystack');
		}

		if (ReferralSettings::status() && $request->is('admin/invoices')) {
			return redirect()->route('referral.admin.invoices');
		}

		if (ReferralSettings::status() && $request->is('admin/invoices/*/approve')) {
			return redirect()->route('referral.admin.invoices.approve', [
				'invoice' => $request->segment(3)
			]);
		}

		return $next($request);
	}
}
