
<div class="row justify-content-center p-1">
	<div class="card col-md-12">
		<div class="card-head mt-1 d-flex justify-content-between">
			<h5><i class="fa fa-star"></i> {{ __('referral::locale.labels.earnings') }}</h5>
		</div>

		<div class="card-body mb-25">
			<section id="datatables-basic-earnings">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<table id="earnings-table" class="table datatables-basic-earnings table-sm">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>{{__('referral::locale.referral_bonuses.from')}} </th>
										<th>{{__('referral::locale.referrals.bonus')}}</th>
										<th>{{__('referral::locale.referral_bonuses.type')}}</th>
										<th>{{__('referral::locale.labels.status')}}</th>
										<th>{{__('referral::locale.labels.created_at')}}</th>
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