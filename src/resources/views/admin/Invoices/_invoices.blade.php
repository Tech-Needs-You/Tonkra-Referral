<section id="datatables-basic">
	<div class="mb-2 mt-">
		@can('view invoices')
		<div class="btn-group">
			<button class="btn btn-primary fw-bold dropdown-toggle" type="button" id="bulk_actions" data-bs-toggle="dropdown"
				aria-expanded="false">
				{{ __('locale.labels.actions') }}
			</button>
			<div class="dropdown-menu" aria-labelledby="bulk_actions">
				<a class="dropdown-item bulk-delete" href="#"><i data-feather="trash"></i> {{
					__('locale.datatables.bulk_delete') }}</a>
			</div>
		</div>
		@endcan
	</div>


	<div class="row">
		<div class="col-12">
			<div class="card">
				<table class="table datatables-basic">
					<thead>
						<tr>
							<th></th>
							<th></th>
							{{-- <th></th> --}}
							{{-- <th>{{ __('locale.labels.id') }}</th> --}}
							<th>#</th>
							{{-- <th>{{__('locale.labels.date')}}</th> --}}
							<th>{{__('locale.menu.Customer')}}</th>
							<th>{{__('locale.labels.type')}}</th>
							<th>{{__('locale.labels.details')}}</th>
							<th>{{__('locale.labels.amount')}}</th>
							<th>{{__('locale.labels.status')}}</th>
							<th></th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</section>