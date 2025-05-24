@php
	use Tonkra\Referral\Models\ReferralRedemption;
@endphp


<div class="row p-1 d-block d-md-none">
	@foreach ([ReferralRedemption::PAYOUT_SMS, ReferralRedemption::PAYOUT_WALLET, ReferralRedemption::PAYOUT_BANK, ReferralRedemption::PAYOUT_TRANSFER] as $type)
		<div class="col-lg-3 col-md-3 mb-1">
			<div class="stat-card bg-light-primary p-1 rounded-3">
				<div class="d-flex justify-content-between align-items-start">
					<div>
						<h3 class="mb-0 fw-bold">{{ (int)$redemptionStats[$type]['amount'] }}</h3>
						<h6 class="text-muted">{{ (__("referral::locale.referral_redemptions.$type")) }}</h6>
					</div>
					<span class="badge bg-primary bg-opacity-10 text-primary p-1">
						<i class="{{ $redemptionStats[$type]['icon'] }} fs-5"></i>
					</span>
				</div>
			</div>
		</div>
	@endforeach
</div>



<div class="row justify-content-center p-1">
	<div class="card col-md-12">
		<div class="card-head mt-1 d-flex justify-content-between">
			<h4><i class="fa fa-star"></i> {{ __('referral::locale.labels.redemptions') }}</h4>
			<div class="row d-none d-md-block">
				<div class="col-12 d-flex flex-wrap gap-1 gap-md-1 justify-content-between align-items-center">
					@foreach ([ReferralRedemption::PAYOUT_SMS, ReferralRedemption::PAYOUT_WALLET, ReferralRedemption::PAYOUT_TRANSFER] as $type)
							<div class="dstat-item bg-light-{{$redemptionStats[$type]['color']}} p-half rounded-3 border-1 border-{{$redemptionStats[$type]['color']}}">
									<div class="mb-1 mb-md-0">
											<span class="d me-2"><i class="{{ $redemptionStats[$type]['icon'] }} me-1"></i> {{ (__("referral::locale.referral_redemptions.$type")) }}:</span>
											<strong>{{ (int)$redemptionStats[$type]['amount'] }}</strong>
									</div>
							</div>
					@endforeach
			</div>
			</div>
		</div>

		<div class="card-body mb-25">
			<section id="datatables-basic2">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<table id="redemptions-table" class="table datatables-basic-redemptions ">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>{{__('referral::locale.referral_redemptions.request_id')}}</th>
										<th>{{__('referral::locale.referrals.downliner')}} </th>
										<th>{{__('referral::locale.referrals.amount')}}</th>
										<th>{{__('referral::locale.referrals.payout_method')}}</th>
										<th>{{__('referral::locale.labels.status')}}</th>
										<th>{{__('referral::locale.referral_redemptions.processed_at')}}</th>
										{{-- <th>{{__('referral::locale.labels.actions')}}</th> --}}
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>