<section id="datatables-basic-redemptions">
	{{-- <div class="mb-2 mt-">
		@can('view invoices')
		<div class="btn-group">
			<button class="btn btn-primary fw-bold dropdown-toggle" type="button" id="bulk_actions" data-bs-toggle="dropdown"
				aria-expanded="false">
				{{ __('referral::locale.labels.actions') }}
			</button>
			<div class="dropdown-menu" aria-labelledby="bulk_actions">
				<a class="dropdown-item bulk-delete" href="#"><i data-feather="trash"></i> {{
					__('referral::locale.datatables.bulk_delete') }}</a>
			</div>
		</div>
		@endcan
	</div> --}}


	<div class="row">
		<div class="col-12">
			<div class="card">
				<table class="table datatables-basic-redemptions compact ">
					<thead>
						<tr>
							<th></th> <!-- Responsive control -->
							<th></th> <!-- Checkbox -->
							<th>{{ __('referral::locale.referral_redemptions.request_id') }}</th>
							<th>{{__('referral::locale.referral_redemptions.recipient')}}</th>
							<th>{{__('referral::locale.referral_redemptions.amount')}}</th>
							<th>{{__('referral::locale.referral_redemptions.type')}}</th>
							<th>{{__('referral::locale.referral_redemptions.status')}}</th>
							<th>{{__('referral::locale.labels.actions')}}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</section>