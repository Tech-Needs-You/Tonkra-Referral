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

		return $next($request);
	}
}
