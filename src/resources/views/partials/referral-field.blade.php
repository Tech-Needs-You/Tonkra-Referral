@php
	$referralCode= '';
	if ($with_referrer) {
		$referralCode = $referrer;
	}
@endphp

@if(config('referral.status'))
	<div class="mt-4">
		<label for="referral_code" class="block font-medium text-sm text-gray-700">Referral Code (optional)</label>
		<input id="referral_code" type="text" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
			name="referral_code" value="{{ $referralCode == '' ? old('referral_code') : $referralCode }}">
	</div>