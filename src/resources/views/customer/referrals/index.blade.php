@php use Tonkra\Referral\Models\ReferralBonus; use Tonkra\Referral\Facades\ReferralSettings; @endphp;

@extends('layouts/contentLayoutMaster')

@section('title', __('referral::locale.labels.referral'))


@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
@endsection

@section('page-style')
<style>
	.action-trigger.active {
    font-weight: bold;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
	}
	.dropdown-form {
			transition: all 0.3s ease;
	}

	.nav-pills #info-tab.nav-link {
		color: var(--bs-info);
	}

	.nav-pills #info-tab.nav-link.active {
		background-color: var(--bs-info);
		border-color: var(--bs-info);
		font-weight: bolder;
		color: #fff;
	}

	.nav-pills #earnings-tab.nav-link {
		color: var(--bs-primary);
	}

	.nav-pills #earnings-tab.nav-link.active {
		background-color: var(--bs-primary);
		border-color: var(--bs-primary);
		font-weight: bolder;
		color: #fff;
	}

	.nav-pills #preferences-tab.nav-link {
		color: var(--bs-dark);
	}

	.nav-pills #preferences-tab.nav-link.active {
		background-color: var(--bs-dark);
		border-color: var(--bs-dark);
		font-weight: bolder;
		color: #fff;
	}

	.nav-pills #redemptions-tab.nav-link {
		color: var(--bs-success);
	}

	.nav-pills #redemptions-tab.nav-link.active {
		background-color: var(--bs-success);
		border-color: var(--bs-success);
		font-weight: bolder;
		color: #fff;
	}

	.p-half {
		padding: .5rem;
	}
</style>
@endsection


@section('content')

<div class="row match-height">

	@foreach([ReferralBonus::STATUS_PAID, ReferralBonus::STATUS_REDEEMED, ReferralBonus::STATUS_EARNED,
	ReferralBonus::STATUS_REJECTED] as $type)
	<div class="col-lg-3 col-sm-6 col-12 ">
		<div class="card">
			<div class="card-header" style="padding:.8rem;">
				<div>
					<h2 class="fw-bolder mb-0">
						{{-- <sup>{{ $referralStats[$type]['count'] }}</sup> / --}}
						{{ (int)$referralStats[$type]['amount'] }}
					</h2>
					<p class="card-text">{{ (__("referral::locale.referral_bonuses.$type")) }}</p>
				</div>
				<a href="{{route('customer.reports.campaigns')}}">
					<div class="avatar bg-light-info p-50 m-0">
						<div class="avatar-content">
							<i class="{{ $referralStats[$type]['icon'] }} font-medium-5"></i>
						</div>
					</div>
				</a>
			</div>
		</div>
	</div>
	@endforeach
</div>

<section id="vertical-tabs">
	<div class="row match-height">
		<div class="col-12">

			<ul class="col-12 nav nav-pills mb-1 row gx-1" role="tablist">
				@if ((bool)$referral_preference?->get('status'))
				<li class="nav-item mb-1 col-6 col-sm-auto">
					<a class="nav-link text-uppercase p-half w-100 border-1 border-info rounded @if (old('tab') == 'info' || (request()->input('tab') == 'info' || request()->input('tab') == null)) active @endif"
						id="info-tab" data-bs-toggle="tab" aria-controls="info" href="#info" role="tab" aria-selected="true">
						<i class="fa fa-circle-info"></i> {{ __('referral::locale.referrals.info') }}
					</a>
				</li>
				@endif
			
				@if ($user->referralBonuses()->exists())
				<li class="nav-item mb-1 col-6 col-sm-auto">
					<a class="nav-link text-uppercase w-100 border-1 border-primary rounded @if (old('tab') == 'earnings' || request()->input('tab') == 'earnings') active @endif"
						id="earnings-tab" data-bs-toggle="tab" aria-controls="earnings" href="#earnings" role="tab" aria-selected="false">
						<i class="fa fa-coins"></i> {{__('referral::locale.labels.earnings') }}
					</a>
				</li>
				@endif

				@if ($user->referralRedemptions()->exists())
				<li class="nav-item mb-1 col-6 col-sm-auto">
					<a class="nav-link text-uppercase w-100 border-1 border-success rounded @if (old('tab') == 'redemptions' || request()->input('tab') == 'redemptions') active @endif"
						id="redemptions-tab" data-bs-toggle="tab" aria-controls="redemptions" href="#redemptions" role="tab"
						aria-selected="false"> <i class="fa fa-star"></i> {{__('referral::locale.labels.redemptions')}} </a>
				</li>
				@endif

				<li class="nav-item mb-1 col-6 col-sm-auto">
					<a class="nav-link text-uppercase p-half border-1 border-dark rounded @if ((old('tab') == 'preferences' || request()->input('tab') == 'preferences') || !(bool)$referral_preference?->get('status')) active @endif"
						id="preferences-tab" data-bs-toggle="tab" aria-controls="preferences" href="#preferences" role="tab"
						aria-selected="false"> <i class="fa fa-cogs"></i> {{__('referral::locale.labels.preferences')}} </a>
				</li>

				@if (ReferralSettings::minSmsRedeemStatus() || ReferralSettings::minWithdrawalRedeemStatus() || ReferralSettings::minTransferRedeemStatus())
					<li class="nav-item col-6 col-sm-auto ms-auto">
						<div class="dropdown w-100">
								<button type="button" 
												class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center"
												data-bs-toggle="dropdown"
												aria-expanded="false"
												aria-haspopup="true"
												id="redeemBonusDropdown">
										{{-- @if ((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] < ReferralSettings::minRedeemAmount())
												<i class="fas fa-exclamation-triangle text-danger me-2" aria-hidden="true"></i>
												<span class="visually-hidden">Warning:</span>
										@else --}}
												<i class="fas fa-thumbs-up text-success me-2" aria-hidden="true"></i>
										{{-- @endif --}}
										{{__('referral::locale.referral_bonuses.redeem')}}
								</button>
								
								<ul class="dropdown-menu dropdown-menu-end p-2" 
										style="min-width: 320px"
										aria-labelledby="redeemBonusDropdown">
										{{-- @if ((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] < ReferralSettings::minRedeemAmount())
												<li class="dropdown-item-text">
														<div class="alert alert-warning mb-0 d-flex align-items-center">
																<i class="fas fa-info-circle me-2" aria-hidden="true"></i>
																<div>
																		Minimum redeem amount is {{ ReferralSettings::minRedeemAmount() }}
																		<div class="small">Current balance: {{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}</div>
																</div>
														</div>
												</li>
										@else --}}
										@if (ReferralSettings::minSmsRedeemStatus())
											<li class="mb-2">
													<button type="button" 
																	class="btn btn-outline-info w-100 d-flex justify-content-between align-items-center action-trigger"
																	data-target="redeemForm"
																	aria-expanded="false">
															<span>Redeem as SMS Unit</span>
															<i class="fas fa-mobile-alt ms-2" aria-hidden="true"></i>
													</button>
													<div class="dropdown-form p-1 w-100 border border-info rounded mt-2" id="redeemForm" style="display: none;">
															<form method="post" action="{{ route('referral.customer.bonus.redeem') }}">
																	@csrf
																	<h6 class="fw-bold text-center mb-2">Redeem as SMS Unit</h6>
																	<div class="mb-3">
																			<label for="redeemAmount" class="form-label">Amount (Available: {{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }})</label>
																			<input type="number" 
																						class="form-control" 
																						id="redeemAmount" 
																						name="amount" 
																						min="{{ ReferralSettings::minSmsRedeemAmount() }}" 
																						max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" 
																						value="{{ min((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'], max(ReferralSettings::minSmsRedeemAmount(), (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'])) }}"
																						aria-describedby="redeemHelp">
																			<div id="redeemHelp" class="form-text">Minimum: {{ ReferralSettings::minSmsRedeemAmount() }}</div>
																	</div>
																	<button type="submit" class="btn btn-primary w-100">Redeem Now</button>
															</form>
													</div>
											</li>
										@endif
										
										@if (ReferralSettings::minWithdrawalRedeemStatus())
											<li class="mb-2">
													<button type="button" 
																	class="btn btn-outline-success w-100 d-flex justify-content-between align-items-center action-trigger"
																	data-target="withdrawForm"
																	aria-expanded="false">
															<span>Withdraw Funds</span>
															<i class="fas fa-money-bill-wave ms-2" aria-hidden="true"></i>
													</button>
													<div class="dropdown-form p-1 w-100 border border-success mt-2 rounded" id="withdrawForm" style="display: none;">
															<form method="post" action="{{ route('referral.customer.bonus.withdraw') }}">
																	@csrf
																	<h6 class="fw-bold text-center mb-1">Withdraw Funds</h6>
																	<div class="mb-1">
																			<label for="amount" class="form-label required">Amount (Available: {{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }})</label>
																			<input type="number" 
																						class="form-control @error('amount') is-invalid @enderror" 
																						id="amount" 
																						name="amount" 
																						min="{{ ReferralSettings::minWithdrawalRedeemAmount() }}" 
																						max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" 
																						value="{{ min((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'], max(ReferralSettings::minWithdrawalRedeemAmount(), (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'])) }}"
																						aria-describedby="withdrawHelp">
																			<div id="withdrawHelp" class="form-text">Minimum: {{ ReferralSettings::minWithdrawalRedeemAmount() }}</div>
																	</div>
																	<div class="mb-1">
																			<label for="network" class="form-label required">Network</label>
																			<select class="form-control select2" id="network" name="network" aria-describedby="network">
																				<option value="" selected disabled>--  {{ __('referral::locale.labels.select') }} -- </option>
																				@foreach (ReferralSettings::withdrawalNetworks() as $network)
																					<option value="{{$network}}"> {{strtoupper($network)}} </option>			
																				@endforeach		
																			</select>
																	</div>
																	<div class="mb-1">
																			<label for="accountNumber" class="form-label required">Account Number</label>
																			<div class="input-group">
																				<div style="width: 8rem">
																					<select class="form-select select2" name="country_code" id="country_code" required>
																						@foreach (Helper::countries() as $country)
																							<option class="" value="{{ $country['d_code'] }}"
																								{{ strtolower(config('app.country')) == strtolower($country['name']) ? 'selected' : null }}>
																								{{ $country['code'] }}({{ $country['d_code'] }})</option>
																						@endforeach
																					</select>
																				</div>
						
																				<input type="text" id="accountNumber"
																					class="form-control @error('accountNumber') is-invalid @enderror"
																					value="{{ old('accountNumber', $accountNumber ?? null) }}" name="accountNumber"
																					placeholder="{{ __('referral::locale.labels.account_number') }}" aria-describedby="accountNumber" required>
																			</div>
																	</div>
																	<div class="mb-1">
																			<label for="accountName" class="form-label required @error('accountNumber') is-invalid @enderror">Account Name</label>
																			<input type="text" 
																						class="form-control" 
																						id="accountName" 
																						name="accountName" 
																						value="{{ old('accountName', $accountName ?? null) }}"
																						aria-describedby="accountName" required>
																	</div>
																	<button type="submit" class="btn btn-primary w-100">Request Withdrawal</button>
															</form>
													</div>
											</li>
										@endif
										
										@if (ReferralSettings::minTransferRedeemStatus())
											<li>
													<button type="button" 
																	class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center action-trigger"
																	data-target="transferForm"
																	aria-expanded="false">
															<span>Transfer to User</span>
															<i class="fas fa-user-friends ms-2" aria-hidden="true"></i>
													</button>
													<div class="dropdown-form p-1 w-100 border border-primary mt-2 rounded" id="transferForm" style="display: none;">
															<form method="post" action="{{ route('referral.customer.bonus.transfer') }}">
																	@csrf
																	<h6 class="fw-bold text-center mb-2">Transfer to Another User</h6>
																	<div class="mb-1">
																			<label for="recipient" class="form-label required">Recipient Referral Code</label>
																			<input type="text" 
																						class="form-control" 
																						id="recipient" 
																						name="recipient" 
																						placeholder="Enter recipient's referral code"
																						aria-describedby="recipientHelp">
																			<div id="recipientHelp" class="form-text">Enter the recipient's referral code</div>
																	</div>
																	<div class="mb-1">
																			<label for="transferAmount" class="form-label required">Amount (Available: {{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }})</label>
																			<input type="number" 
																						class="form-control" 
																						id="transferAmount" 
																						name="amount" 
																						min="{{ ReferralSettings::minTransferRedeemAmount() }}" 
																						max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" 
																						value="{{ min((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'], max(ReferralSettings::minTransferRedeemAmount(), (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'])) }}"
																						aria-describedby="transferHelp">
																			<div id="transferHelp" class="form-text">Minimum: {{ ReferralSettings::minTransferRedeemAmount() }}</div>
																	</div>
																	<button type="submit" class="btn btn-primary w-100">Confirm Transfer</button>
															</form>
													</div>
											</li>
										@endif
										{{-- @endif --}}
								</ul>
						</div>
					</li>
				@endif
		</ul>

		<div class="tab-content">

			@if ((bool)$referral_preference?->get('status'))
			<div
				class="tab-pane fade @if (old('tab') == 'info' || (request()->input('tab') == 'info' || request()->input('tab') == null))  show active @endif"
				id="info" role="tabpanel" aria-labelledby="info-tab">
				@include('referral::customer.referrals._info')
			</div>
			@endif

			<div
				class="tab-pane fade @if (old('tab') == 'preferences' || (request()->input('tab') == 'downliners')  || !(bool)$referral_preference?->get('status'))  show active @endif"
				id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
				@include('referral::customer.referrals._preferences')
			</div>

			@if ($user->referralBonuses()->exists())
			<div
				class="tab-pane fade @if (old('tab') == 'earnings' || request()->input('tab') == 'earnings')  show active @endif"
				id="earnings" role="tabpanel" aria-labelledby="earnings-tab">
				@include('referral::customer.referrals._earnings')
			</div>
			@endif

			@if ($user->referralRedemptions()->exists())
			<div
				class="tab-pane fade @if (old('tab') == 'redemptions' || request()->input('tab') == 'redemptions')  show active @endif"
				id="redemptions" role="tabpanel" aria-labelledby="redemptions-tab">
				@include('referral::customer.referrals._redemptions')
			</div>
			@endif
		</div>
	</div>
	</div>
</section>
@endsection

@section('vendor-script')
<!-- vendor files -->
<script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>

<script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection


@section('page-script')

<script>
	$(document).ready(function () {
				"use strict"

				// Prevent dropdown from closing when clicking inside forms
				$('.dropdown-form').on('click', function(e) {
				e.stopPropagation();
				});
				
				// Handle action-trigger clicks
				$('.action-trigger').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation(); // Prevent dropdown from closing
				
				const targetId = $(this).data('target');
				const $targetForm = $('#' + targetId);
				const isExpanded = $(this).attr('aria-expanded') === 'true';
				
				// Close all forms first
				$('.dropdown-form').hide();
				$('.action-trigger').attr('aria-expanded', 'false').removeClass('active');
				
				// Toggle the clicked one if it wasn't already expanded
				if (!isExpanded) {
				$targetForm.show();
				$(this).attr('aria-expanded', 'true').addClass('active');
				}
				
				// Keep the parent dropdown open
				$(this).closest('.dropdown-menu').addClass('show');
				$(this).closest('.dropdown').find('.dropdown-toggle').attr('aria-expanded', 'true');
				});
				
				// Close forms when clicking outside
				$(document).on('click', function(e) {
				if (!$(e.target).closest('.dropdown-menu').length) {
				$('.dropdown-form').hide();
				$('.action-trigger').attr('aria-expanded', 'false').removeClass('active');
				}
				});
				
				// init table dom
				let Table = $("table[id=downliners-table]");
				let RedemptionsTable = $("table[id=redemptions-table]");
				
				// init list view datatable
				let dataListView = $('.datatables-basic-downliners').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.customer.downliners.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
								{"data": 'responsive_id', orderable: false, searchable: false},
								{"data": "uid"},
								{"data": "uid"},
								{"data": "name"},
								{"data": "earned_bonus", orderable: false, searchable: false},
								{"data": "balance", orderable: false, searchable: false},
								{"data": "phone", orderable: false, searchable: false},
								{"data": "status", orderable: false, searchable: false},
								{"data": "action", orderable: false, searchable: false}
						],
						searchDelay: 500,
						columnDefs: [
								{
										// For Responsive
										className: 'control',
										orderable: false,
										responsivePriority: 2,
										targets: 0
								},
								{
										// For Checkboxes
										targets: 1,
										orderable: false,
										responsivePriority: 3,
										render: function (data) {
												return (
														'<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
														data +
														'" /><label class="form-check-label" for="' +
														data +
														'"></label></div>'
												);
										},
										checkboxes: {
												selectAllRender:
														'<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
												selectRow: true
										}
								},
								{
										targets: 2,
										visible: false
								},
								{
										// Avatar image/badge, Name and post
										targets: 3,
										responsivePriority: 1,
										render: function (data, type, full) {
												var $user_img = full['avatar'],
														$name = full['name'],
														$created_at = full['created_at'],
														$email = full['email'];
												if ($user_img) {
														// For Avatar image
														var $output =
																'<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
												} else {
														// For Avatar badge
														var stateNum = full['status'];
														var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
														var $state = states[stateNum],
																$name = full['name'],
																$initials = $name.match(/\b\w/g) || [];
														$initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
														$output = '<span class="avatar-content">' + $initials + '</span>';
												}
												var colorClass = $user_img === '' ? ' bg-light-' + $state + ' ' : '';
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="avatar ' +
														colorClass +
														' me-1">' +
														$output +
														'</div>' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold">' +
														$name +
														'</span>' +
														'<small class="emp_post text-truncate text-muted">' +
														$created_at +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Status
										targets: 7,
										responsivePriority: 4,
										render: function (data, type, full) {
												var $status 			= full['status'],
														$status_color = full['status_color'],
														$status_label = full['status_label'];
												
												// Creates full output for row
												return '<a nohref class="'+ $status_color +' px-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + $status_label + '">' +
														feather.icons[$status].toSvg({class: 'font-medium-5'}) +
														'</a>';
										}
								},
								{
										// Actions
										targets: -1,
										title: '{{ __('locale.labels.actions') }}',
										orderable: false,
										render: function (data, type, full) {
												var $super_user = '';
												
												return (
														$super_user +
														'<a class="copy text-primary pe-1" data-text="'+ full['copy'] +'" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['copy_label'] + '" >' +
														feather.icons['copy'].toSvg({class: 'font-medium-4'}) +
														'</a>'//+
														// '<a nohref="' + full['top_up'] + '" class="text-success pe-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['top_up_label'] + '" onclick="alert(\'Under construction\')">' +
														// feather.icons['trending-up'].toSvg({class: 'font-medium-4'}) +
														// '</a>'+
														// '<a nohref="' + full['report'] + '" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['report_label'] + '" onclick="alert(\'Under construction\')">' +
														// feather.icons['flag'].toSvg({class: 'font-medium-4'}) +
														// '</a>'
												);
										}
								}
						],
						dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
						language: {
								paginate: {
										// remove previous & next text from pagination
										previous: '&nbsp;',
										next: '&nbsp;'
								},
								sLengthMenu: "_MENU_",
								sZeroRecords: "{{ __('locale.datatables.no_results') }}",
								sSearch: "{{ __('locale.datatables.search') }}",
								sProcessing: "{{ __('locale.datatables.processing') }}",
								sInfo: "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
						},
						responsive: {
								details: {
										display: $.fn.dataTable.Responsive.display.modal({
												header: function (row) {
														let data = row.data();
														return 'Details of ' + data['name'];
												}
										}),
										type: 'column',
										renderer: function (api, rowIdx, columns) {
												let data = $.map(columns, function (col) {
														return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
																? '<tr data-dt-row="' +
																col.rowIdx +
																'" data-dt-column="' +
																col.columnIndex +
																'">' +
																'<td>' +
																col.title +
																':' +
																'</td> ' +
																'<td>' +
																col.data +
																'</td>' +
																'</tr>'
																: '';
												}).join('');
												return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
										}
								}
						},
						aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
						select: {
								style: "multi"
						},
						order: [[2, "desc"]],
						displayLength: 10,
				});
				
				// init list view datatable
				let redemptionsDataListView = $('.datatables-basic-redemptions').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.customer.redemptions.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
								{"data": 'responsive_id', orderable: false, searchable: false},
								{"data": "uid"},
								{"data": "request_id"},
								{"data": "downliner", visible: false},
								{"data": "amount"},
								{"data": "payout_method"},
								{"data": "status"},
								{"data": "processed_at", visible: false},
								// {"data": "actions", orderable: false, searchable: false}
						],
						searchDelay: 500,
						columnDefs: [
								{
										// For Responsive
										className: 'control',
										orderable: false,
										responsivePriority: 2,
										targets: 0
								},
								{
										// For Checkboxes
										targets: 1,
										orderable: false,
										responsivePriority: 3,
										render: function (data) {
												return (
														'<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
														data +
														'" /><label class="form-check-label" for="' +
														data +
														'"></label></div>'
												);
										},
										checkboxes: {
												selectAllRender:
														'<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
												selectRow: true
										}
								},
								{
										// Avatar image/badge, Name and post
										targets: 3,
										responsivePriority: 1,
										render: function (data, type, full) {
												var $user_img = full['avatar'],
														$name = full['downliner_name'],
														$created_at = full['created_at']
												if ($user_img) {
														// For Avatar image
														var $output = '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
												} else {
														// For Avatar badge
														var stateNum = full['status'];
														var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
														var $state = states[stateNum], $initials = $name.match(/\b\w/g) || [];
														$initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
														$output = '<span class="avatar-content">' + $initials + '</span>';
												}
												var colorClass = $user_img === '' ? ' bg-light-' + $state + ' ' : '';
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="avatar ' +
														colorClass +
														' me-1">' +
														$output +
														'</div>' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold text-sm">' +
														$name +
														'</span>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Avatar image/badge, Name and post
										targets: 4,
										responsivePriority: 2,
										render: function (data, type, full) {
												var $amount = full['amount'],
														$processed_at = full['processed_at']
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold">' +
														$amount +
														'</span>' +
														'<small class="emp_post text-truncate text-muted">' +
														$processed_at +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Status
										targets: 6,
										responsivePriority: 4,
								},
								// {
								// 		// Actions
								// 		targets: -1,
								// 		title: '{{ __('locale.labels.actions') }}',
								// 		orderable: false,
								// 		render: function (data, type, full) {
								// 				var $super_user = '';
												
								// 				return (
								// 						$super_user +
								// 						'<a class="copy text-primary pe-1" data-text="'+ full['copy'] +'" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['copy_label'] + '" >' +
								// 						feather.icons['copy'].toSvg({class: 'font-medium-4'}) +
								// 						'</a>'//+
								// 						// '<a nohref="' + full['top_up'] + '" class="text-success pe-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['top_up_label'] + '" onclick="alert(\'Under construction\')">' +
								// 						// feather.icons['trending-up'].toSvg({class: 'font-medium-4'}) +
								// 						// '</a>'+
								// 						// '<a nohref="' + full['report'] + '" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['report_label'] + '" onclick="alert(\'Under construction\')">' +
								// 						// feather.icons['flag'].toSvg({class: 'font-medium-4'}) +
								// 						// '</a>'
								// 				);
								// 		}
								// }
						],
						dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
						language: {
								paginate: {
										// remove previous & next text from pagination
										previous: '&nbsp;',
										next: '&nbsp;'
								},
								sLengthMenu: "_MENU_",
								sZeroRecords: "{{ __('locale.datatables.no_results') }}",
								sSearch: "{{ __('locale.datatables.search') }}",
								sProcessing: "{{ __('locale.datatables.processing') }}",
								sInfo: "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
						},
						responsive: {
								details: {
										display: $.fn.dataTable.Responsive.display.modal({
												header: function (row) {
														let data = row.data();
														return 'Details of ' + data['name'];
												}
										}),
										type: 'column',
										renderer: function (api, rowIdx, columns) {
												let data = $.map(columns, function (col) {
														return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
																? '<tr data-dt-row="' +
																col.rowIdx +
																'" data-dt-column="' +
																col.columnIndex +
																'">' +
																'<td>' +
																col.title +
																':' +
																'</td> ' +
																'<td>' +
																col.data +
																'</td>' +
																'</tr>'
																: '';
												}).join('');
												return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
										}
								}
						},
						aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
						select: {
								style: "multi"
						},
						order: [[2, "desc"]],
						displayLength: 10,
				});
				
				// init list view datatable
				let earningssDataListView = $('.datatables-basic-earnings').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.customer.earnings.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
								{"data": 'responsive_id', orderable: false, searchable: false},
								{"data": "uid"},
								{"data": "from"},
								{"data": "bonus"},
								{"data": "type"},
								{"data": "status"},
								{"data": "created_at"},
								// {"data": "actions", orderable: false, searchable: false}
						],
						searchDelay: 500,
						columnDefs: [
								{
										// For Responsive
										className: 'control',
										orderable: false,
										responsivePriority: 2,
										targets: 0
								},
								{
										// For Checkboxes
										targets: 1,
										orderable: false,
										responsivePriority: 3,
										render: function (data) {
												return (
														'<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
														data +
														'" /><label class="form-check-label" for="' +
														data +
														'"></label></div>'
												);
										},
										checkboxes: {
												selectAllRender:
														'<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
												selectRow: true
										}
								},
								{
										// Avatar image/badge, Name and post
										targets: 2,
										responsivePriority: 1,
										render: function (data, type, full) {
												var $user_img = full['avatar'],
														$from = full['from'],
														$created_at = full['created_at']
												if ($user_img) {
														// For Avatar image
														var $output = '<img src="' + $user_img + '" alt="Avatar" width="32" height="32">';
												} else {
														// For Avatar badge
														var stateNum = full['status'];
														var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
														var $state = states[stateNum], $initials = $from.match(/\b\w/g) || [];
														$initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
														$output = '<span class="avatar-content">' + $initials + '</span>';
												}
												var colorClass = $user_img === '' ? ' bg-light-' + $state + ' ' : '';
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="avatar ' +
														colorClass +
														' me-1">' +
														$output +
														'</div>' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold text-sm">' +
														$from +
														'</span>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Avatar image/badge, Name and post
										targets: 3,
										responsivePriority: 2,
										render: function (data, type, full) {
												var $bonus = full['bonus'],
														$is_partially_redeemed = full['is_partially_redeemed'],
														$original_amount = full['original_amount'],
														$remaining = $is_partially_redeemed ? '<small class="emp_post text-truncate text-muted">' + $original_amount - $bonus + '</small>' : '' ;
														
												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="d-flex flex-column">' +
														'<span class="emp_name text-truncate fw-bold">' +
														$bonus +
														'</span>' +
														 $remaining+
														'</div>' +
														'</div>';
										}
								},
								{
										// Status
										targets: 5,
										responsivePriority: 4,
								},
								// {
								// 		// Actions
								// 		targets: -1,
								// 		title: '{{ __('locale.labels.actions') }}',
								// 		orderable: false,
								// 		render: function (data, type, full) {
								// 				var $super_user = '';
												
								// 				return (
								// 						$super_user +
								// 						'<a class="copy text-primary pe-1" data-text="'+ full['copy'] +'" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['copy_label'] + '" >' +
								// 						feather.icons['copy'].toSvg({class: 'font-medium-4'}) +
								// 						'</a>'//+
								// 						// '<a nohref="' + full['top_up'] + '" class="text-success pe-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['top_up_label'] + '" onclick="alert(\'Under construction\')">' +
								// 						// feather.icons['trending-up'].toSvg({class: 'font-medium-4'}) +
								// 						// '</a>'+
								// 						// '<a nohref="' + full['report'] + '" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['report_label'] + '" onclick="alert(\'Under construction\')">' +
								// 						// feather.icons['flag'].toSvg({class: 'font-medium-4'}) +
								// 						// '</a>'
								// 				);
								// 		}
								// }
						],
						dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
						language: {
								paginate: {
										// remove previous & next text from pagination
										previous: '&nbsp;',
										next: '&nbsp;'
								},
								sLengthMenu: "_MENU_",
								sZeroRecords: "{{ __('locale.datatables.no_results') }}",
								sSearch: "{{ __('locale.datatables.search') }}",
								sProcessing: "{{ __('locale.datatables.processing') }}",
								sInfo: "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
						},
						responsive: {
								details: {
										display: $.fn.dataTable.Responsive.display.modal({
												header: function (row) {
														let data = row.data();
														return 'Details of ' + data['name'];
												}
										}),
										type: 'column',
										renderer: function (api, rowIdx, columns) {
												let data = $.map(columns, function (col) {
														return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
																? '<tr data-dt-row="' +
																col.rowIdx +
																'" data-dt-column="' +
																col.columnIndex +
																'">' +
																'<td>' +
																col.title +
																':' +
																'</td> ' +
																'<td>' +
																col.data +
																'</td>' +
																'</tr>'
																: '';
												}).join('');
												return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
										}
								}
						},
						aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
						select: {
								style: "multi"
						},
						order: [[6, "desc"]],
						displayLength: 10,
				});

				$("body").on('click', '.copy', function (e) {
					e.preventDefault()
					copyToClipboard($(this).data('text'));
				})
			});
</script>
@endsection