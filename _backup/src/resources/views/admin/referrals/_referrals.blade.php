@php
use Tonkra\Referral\Models\ReferralRedemption;
@endphp



<div class="row justify-content-center p-1">
	<div class="card col-md-12">
		<div class="card-head mt-1 d-flex justify-content-between">
			<h5>{{ __('referral::locale.labels.referrals') }}</h5>
		</div>

		<div class="card-body mb-25">
			<section id="datatables-basic1">
			
				<div class="row">
					<div class="col-12">
						<div class="card">
							<table id="referrals-table" class="table datatables-basic-referrals table-sm">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>{{ __('referral::locale.labels.id') }}</th>
										<th>{{__('referral::locale.labels.name')}} </th>
										<th>{{__('referral::locale.referrals.earned_bonus')}}</th>
										<th>{{__('referral::locale.labels.balance')}}</th>
										<th>{{__('referral::locale.labels.status')}}</th>
										<th>{{__('referral::locale.labels.created_at')}}</th>
										<th>{{__('referral::locale.labels.actions')}}</th>
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