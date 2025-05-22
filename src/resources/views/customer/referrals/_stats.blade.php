@php use Tonkra\Referral\Models\ReferralBonus; @endphp;

<div class="row match-height">

	@foreach([ReferralBonus::STATUS_PAID, ReferralBonus::STATUS_REDEEMED, ReferralBonus::STATUS_PENDING,
	ReferralBonus::STATUS_REJECTED] as $type)
	<div class="col-lg-3 col-sm-6 col-12">
		<div class="card">
			<div class="card-header" style="padding:.8rem;">
				<div>
					<h2 class="fw-bolder mb-0">
						<sup>{{ $referralStats[$type]['count'] }}</sup>
						/ {{ $referralStats[$type]['amount'] }}
					</h2>
					<p class="card-text">{{ (__("referral::locale.referral_bonuses.$type")) }}</p>
				</div>
				<a href="{{route('customer.reports.campaigns')}}">
					<div class="avatar bg-light-info p-50 m-0">
						<div class="avatar-content">
							<i data-feather="money" class="text-info font-medium-5"></i>
						</div>
					</div>
				</a>
			</div>
		</div>
	</div>
	@endforeach

</div>
<div class="row p-1">
	<div class="card col-md-12">
		<div class="card-head mt-1 d-flex justify-content-between">
			<small><i class="fa fa-heart-pulse"></i> {{ __('referral::locale.labels.my_downlines') }}</small>
			{{-- <span>
				<button class="copy-to-clipboard btn btn-info btn-sm" role="button" data-text="{{ $user->uid }}"
					data-bs-toggle="tooltip" data-bs-placement="bottom"
					title="{{ __('referral::locale.buttons.copy_referral_code') }}"><i
						class="text-white fas fa-copy"></i></button>
				<button class="copy-to-clipboard btn btn-default btn-outline-primary btn-sm" role="button"
					data-text="{{ route('register_with_referrer', $user->uid )}}" data-bs-toggle="tooltip"
					data-bs-placement="bottom" title="{{ __('referral::locale.buttons.copy_referral_link') }}"><i
						class="text-primary fas fa-share-nodes"></i></button>
			</span> --}}
		</div>
		<div class="card-body mb-25">
			<!-- Basic table -->
			<section id="datatables-basic1">
				<div class="mb-1 mt-1">
					<div class="btn-group">
						<button class="btn btn-primary btn-sm fw-bold dropdown-toggle" type="button" id="bulk_actions"
							data-bs-toggle="dropdown" aria-expanded="false">
							{{ __('referral::locale.labels.actions') }}
						</button>
						<div class="dropdown-menu" aria-labelledby="bulk_actions">
							<a class="dropdown-item bulk-top_up" href="#" onclick="alert('Under Construction...')"><i
									data-feather="trending-up" class="text-success">&nbsp;</i> {{
								__('referral::locale.datatables.bulk_top_up') }}</a>
							<a class="dropdown-item bulk-flag" href="#" onclick="alert('Under Construction...')"><i
									data-feather="flag" class="text-danger">&nbsp;</i> {{ __('referral::locale.datatables.bulk_flag')
								}}</a>
						</div>
					</div>

					{{-- <div class="btn-group">
						<a href="{{route('admin.customers.export')}}" class="btn btn-info btn-sm waves-light waves-effect fw-bold">
							{{__('referral::locale.buttons.export')}} <i data-feather="file-text"></i></a>
					</div> --}}
				</div>

				<div class="row">
					<div class="col-12">
						<div class="card">
							<table id="downliners-table" class="table datatables-basic-downliners table-sm">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>{{ __('referral::locale.labels.id') }}</th>
										<th>{{__('referral::locale.labels.name')}} </th>
										<th>{{__('referral::locale.labels.email')}}</th>
										<th>{{__('referral::locale.labels.balance')}}</th>
										<th>{{__('referral::locale.labels.status')}}</th>
										<th>{{__('referral::locale.labels.actions')}}</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</section>
			<!--/ Basic table -->
		</div>
	</div>
</div>