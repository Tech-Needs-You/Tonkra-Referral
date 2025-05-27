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
			.nav-pills #info-tab.nav-link {
				color: var(--bs-primary);
			}
			.nav-pills #info-tab.nav-link.active {
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

{{-- <div class="row match-height">

	@foreach([ReferralBonus::STATUS_PAID, ReferralBonus::STATUS_REDEEMED, ReferralBonus::STATUS_EARNED, ReferralBonus::STATUS_REJECTED] as $type)
		<div class="col-lg-3 col-sm-6 col-12 ">
			<div class="card">
				<div class="card-header" style="padding:.8rem;">
					<div>
						<h2 class="fw-bolder mb-0">
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

			<ul class="nav nav-pills mb-2" role="tablist">

				@if ((bool)$referral_preference?->get('status'))
					<li class="nav-item border-1 border-primary rounded me-1 mb-1">
						<a class="nav-link text-uppercase p-half @if (old('tab') == 'info' || (request()->input('tab') == 'info' || request()->input('tab') == null)) active @endif"
							id="info-tab" data-bs-toggle="tab" aria-controls="info" href="#info" role="tab" aria-selected="true">
							<i class="fa fa-circle-info"></i> {{ __('referral::locale.referrals.info') }}
						</a>
					</li>
				@endif

				<li class="nav-item border-1 border-dark rounded me-1 mb-1">
					<a class="nav-link text-uppercase p-half @if ((old('tab') == 'preferences' || request()->input('tab') == 'preferences') || !(bool)$referral_preference?->get('status')) active @endif"
						id="preferences-tab" data-bs-toggle="tab" aria-controls="preferences" href="#preferences" role="tab"
						aria-selected="false"> <i class="fa fa-cogs"></i> {{__('referral::locale.labels.preferences')}} </a>
				</li>

				@if ($user->referralRedemptions()->exists())
					<li class="nav-item border-1 border-success rounded me-1 mb-1">
						<a class="nav-link text-uppercase @if (old('tab') == 'redemptions' || request()->input('tab') == 'redemptions') active @endif"
							id="redemptions-tab" data-bs-toggle="tab" aria-controls="redemptions" href="#redemptions" role="tab"
							aria-selected="false"> <i class="fa fa-star"></i> {{__('referral::locale.labels.redemptions')}} </a>
					</li>
				@endif

				<li class="nav-item ms-auto">
					<div class="dropdown me-1">
						<button type="button" class="btn border border-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown"
							aria-expanded="false">
							@if ((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] < ReferralSettings::minRedeemAmount())
								<i class="fa fa-triangle-exclamation text-danger"></i> &nbsp;
							@else
								<i class="fa fa-thumbs-up text-success"></i> &nbsp;
							@endif
							{{__('referral::locale.referral_bonuses.redeem')}}
						</button>
				
						<ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px; width: 100%; max-width: 400px">
							@if ((int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] < ReferralSettings::minRedeemAmount())
								<li class="p-1 text-danger text-sm"> Sorry you need a minimum of {{ ReferralSettings::minRedeemAmount() }} to redeem </li>
							@else
								<li class="">
									<div class="dropdown p-1 pb-0">
										<button type="button" class="btn btn-info w-100 dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false"	aria-expanded="false">
											As SMS Unit
										</button>
										<form method="post" action="{{ route('referral.customer.bonus.redeem') }}" class="dropdown-menu p-1 border1 border-info" style="min-width: 300px; width: 100%; max-width: 400px">
											@csrf
											<small class="fw-bold mx-auto">Redeem as SMS Unit</small>
											<div class="mb-1">
												<label for="amount" class="form-label">Amount</label>
												<input type="number" step="1" class="form-control" id="amount" name="amount" min="{{ ReferralSettings::minRedeemAmount() }}" max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" placeholder="Enter amount to redeem" value="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}">
											</div>
											
											<button type="submit" class="btn btn-primary w-100">Redeem</button>
										</form>
									</div>
								</li>
								<li class="">
									<div class="dropdown p-1 pb-0">
										<button type="button" class="btn btn-success w-100 dropdown-toggle" data-bs-toggle="dropdown"
											aria-expanded="false">
											Withdraw
										</button>
										<form method="post" action="{{ route('referral.customer.bonus.withdraw') }}" class="dropdown-menu p-1 border1 border-success" style="min-width: 300px; width: 100%; max-width: 400px">
											@csrf
											<small class="fw-bold mx-auto">Widthraw Monetary value</small>
											<div class="mb-1">
												<label for="amount" class="form-label">Amount</label>
												<input type="number" step="1" class="form-control" id="amount" min="{{ ReferralSettings::minRedeemAmount() }}"
													max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" placeholder="Enter amount to withdraw"
													value="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}">
											</div>
								
											<button type="submit" class="btn btn-primary w-100">Withdraw</button>
										</form>
									</div>
								</li>
								<li class="">
									<div class="dropdown p-1 pb-0">
										<button type="button" class="btn btn-primary w-100 dropdown-toggle" data-bs-toggle="dropdown"
											aria-expanded="false">
											Transfer
										</button>
										<form method="post" action="{{ route('referral.customer.bonus.transfer') }}" class="dropdown-menu p-1 border1 border-primary" style="min-width: 300px; width: 100%; max-width: 400px">
											@csrf
											<small class="fw-bold mx-auto">Transfer to another user</small>
											<div class="mb-1">
												<label for="amount" class="form-label">Recipient</label>
												<input type="text" class="form-control" id="recipient" min="{{ ReferralSettings::minRedeemAmount() }}"
													max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" placeholder="Enter recipient referral code"
													value="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" name="recipient">
													<small class="text-muted">Enter recipient referral code</small>
											</div>
											<div class="mb-1">
												<label for="amount" class="form-label">Amount</label>
												<input type="number" step="1" class="form-control" id="amount" min="{{ ReferralSettings::minRedeemAmount() }}"
													max="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" placeholder="Enter amount to transfer"
													value="{{ (int)$referralStats[ReferralBonus::STATUS_PAID]['amount'] }}" name="amount">
											</div>
								
											<button type="submit" class="btn btn-primary w-100">Transfer</button>
										</form>
									</div>
								</li>
							@endif
						</ul>
					</div>
				</li>
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
				
				@if ($user->referralRedemptions()->exists())
					<div class="tab-pane fade @if (old('tab') == 'redemptions' || request()->input('tab') == 'redemptions')  show active @endif"
						id="redemptions" role="tabpanel" aria-labelledby="redemptions-tab">
						@include('referral::customer.referrals._redemptions')
					</div>
				@endif
			</div>
		</div>
	</div>
</section> --}}

{{-- <div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-md-3">
				<div class="card sticky-top" style="top: calc(62.4px + 18.2px + 1rem);">
					<div class="list-group list-group-flush" id="useradd-sidenav" role="tablist">
						<a href="#transaction" class="list-group-item list-group-item-action border-0 tab-link active" id="transaction-tab"
							data-bs-toggle="tab" role="tab" aria-controls="transaction" aria-selected="false">
							Transaction
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>
						<a href="#payout-request" class="list-group-item list-group-item-action border-0 tab-link" id="payout-request-tab"
							data-bs-toggle="tab" role="tab" aria-controls="payout-request" aria-selected="false">
							Payout Request
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>
						<a href="#settings" class="list-group-item list-group-item-action border-0 tab-link" id="settings-tab"
							data-bs-toggle="tab" role="tab" aria-controls="settings" aria-selected="true">
							Settings
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>
					</div>
				</div>
			</div>

			<div class="col-md-9">
				<div class="tab-content">
					<div class="tab-pane show active" id="transaction" role="tabpanel" aria-labelledby="transaction-tab">
						<div id="transaction" class="card tab-content">
							<div class="card-header">
								<h5>Transaction</h5>
							</div>
							<div class="card-body pb-0 table-border-style">
								<div class="table-responsive">
									<div class="dataTable-wrapper dataTable-loading dataTable-empty no-footer sortable searchable fixed-columns">
										<div class="dataTable-top">
											<div class="dataTable-search"><input class="dataTable-input" placeholder="Search..." type="text">
											</div>
										</div>
										<div class="dataTable-container">
											<table class="table pc-dt-simple dataTable dataTable-table" id="transaction">
												<thead>
													<tr>
														<th data-sortable="" style="width: 7.06851%;"><a href="#" class="dataTable-sorter">#</a></th>
														<th data-sortable="" style="width: 14.0402%;"><a href="#" class="dataTable-sorter">Owner
																Name</a></th>
														<th data-sortable="" style="width: 16.5577%;"><a href="#" class="dataTable-sorter">Referral
																Owner</a></th>
														<th data-sortable="" style="width: 12.5878%;"><a href="#" class="dataTable-sorter">Plan Name</a>
														</th>
														<th data-sortable="" style="width: 12.3941%;"><a href="#" class="dataTable-sorter">Plan
																Price</a></th>
														<th data-sortable="" style="width: 15.9768%;"><a href="#" class="dataTable-sorter">Commission
																(%)</a></th>
														<th data-sortable="" style="width: 21.3992%;"><a href="#" class="dataTable-sorter">Commission
																Amount</a></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td class="dataTables-empty" colspan="7">No entries found</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="dataTable-bottom">
											<div class="dataTable-info"></div>
											<nav class="dataTable-pagination">
												<ul class="dataTable-pagination-list"></ul>
											</nav>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="payout-request" role="tabpanel" aria-labelledby="payout-request-tab">
						<div id="payout-request" class="card tab-content d-none">
							<div class="card-header">
								<h5>Payout Request</h5>
							</div>
							<div class="card-body table-border-style">
								<div class="table-responsive">
									<table class="table pc-dt-simple" id="payout-request">
										<thead>
											<tr>
												<th>#</th>
												<th>Owner Name</th>
												<th>Requested Date</th>
												<th>Requested Amount</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
							</div>
					</div>

					<div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
						<div id="transaction" class="card tab-content d-none">
							<div class="card-header">
								<h5>Transaction</h5>
							</div>
							<div class="card-body pb-0 table-border-style">
								<div class="table-responsive">
									<div class="dataTable-wrapper dataTable-loading dataTable-empty no-footer sortable searchable fixed-columns">
										<div class="dataTable-top">
											<div class="dataTable-dropdown"><label><select class="dataTable-selector">
														<option value="5">5</option>
														<option value="10" selected="">10</option>
														<option value="15">15</option>
														<option value="20">20</option>
														<option value="25">25</option>
													</select> entries per page</label></div>
											<div class="dataTable-search"><input class="dataTable-input" placeholder="Search..." type="text">
											</div>
										</div>
										<div class="dataTable-container">
											<table class="table pc-dt-simple dataTable dataTable-table" id="transaction">
												<thead>
													<tr>
														<th data-sortable="" style="width: 7.06851%;"><a href="#" class="dataTable-sorter">#</a></th>
														<th data-sortable="" style="width: 14.0402%;"><a href="#" class="dataTable-sorter">Owner
																Name</a></th>
														<th data-sortable="" style="width: 16.5577%;"><a href="#" class="dataTable-sorter">Referral
																Owner</a></th>
														<th data-sortable="" style="width: 12.5878%;"><a href="#" class="dataTable-sorter">Plan Name</a>
														</th>
														<th data-sortable="" style="width: 12.3941%;"><a href="#" class="dataTable-sorter">Plan
																Price</a></th>
														<th data-sortable="" style="width: 15.9768%;"><a href="#" class="dataTable-sorter">Commission
																(%)</a></th>
														<th data-sortable="" style="width: 21.3992%;"><a href="#" class="dataTable-sorter">Commission
																Amount</a></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td class="dataTables-empty" colspan="7">No entries found</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="dataTable-bottom">
											<div class="dataTable-info"></div>
											<nav class="dataTable-pagination">
												<ul class="dataTable-pagination-list"></ul>
											</nav>
										</div>
									</div>
								</div>
							</div>
							</div>
					</div>
				</div>
			</div>			

			<div class="col-md-9">


				<!--Site Settings-->
				<div id="transaction" class="card tab-content d-none">
					<div class="card-header">
						<h5>Transaction</h5>
					</div>
					<div class="card-body pb-0 table-border-style">
						<div class="table-responsive">
							<div
								class="dataTable-wrapper dataTable-loading dataTable-empty no-footer sortable searchable fixed-columns">
								<div class="dataTable-top">
									<div class="dataTable-dropdown"><label><select class="dataTable-selector">
												<option value="5">5</option>
												<option value="10" selected="">10</option>
												<option value="15">15</option>
												<option value="20">20</option>
												<option value="25">25</option>
											</select> entries per page</label></div>
									<div class="dataTable-search"><input class="dataTable-input" placeholder="Search..." type="text">
									</div>
								</div>
								<div class="dataTable-container">
									<table class="table pc-dt-simple dataTable dataTable-table" id="transaction">
										<thead>
											<tr>
												<th data-sortable="" style="width: 7.06851%;"><a href="#" class="dataTable-sorter">#</a></th>
												<th data-sortable="" style="width: 14.0402%;"><a href="#" class="dataTable-sorter">Owner
														Name</a></th>
												<th data-sortable="" style="width: 16.5577%;"><a href="#" class="dataTable-sorter">Referral
														Owner</a></th>
												<th data-sortable="" style="width: 12.5878%;"><a href="#" class="dataTable-sorter">Plan Name</a>
												</th>
												<th data-sortable="" style="width: 12.3941%;"><a href="#" class="dataTable-sorter">Plan
														Price</a></th>
												<th data-sortable="" style="width: 15.9768%;"><a href="#" class="dataTable-sorter">Commission
														(%)</a></th>
												<th data-sortable="" style="width: 21.3992%;"><a href="#" class="dataTable-sorter">Commission
														Amount</a></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="dataTables-empty" colspan="7">No entries found</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="dataTable-bottom">
									<div class="dataTable-info"></div>
									<nav class="dataTable-pagination">
										<ul class="dataTable-pagination-list"></ul>
									</nav>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id="payout-request" class="card tab-content d-none">
					<div class="card-header">
						<h5>Payout Request</h5>
					</div>
					<div class="card-body table-border-style">
						<div class="table-responsive">
							<table class="table pc-dt-simple" id="payout-request">
								<thead>
									<tr>
										<th>#</th>
										<th>Owner Name</th>
										<th>Requested Date</th>
										<th>Requested Amount</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div id="settings" class="card tab-content">
					<form method="POST" action="https://storehost.ushmid.com/referral-program" accept-charset="UTF-8"
						enctype="multipart/form-data" class="needs-validation" novalidate=""><input name="_token" type="hidden"
							value="okQW6sk7AxXMPuFFSwC28dq6Ee1YJheSkWOq2Mo5">
						<div class="card-header flex-column flex-lg-row d-flex align-items-lg-center gap-2 justify-content-between">
							<h5>Settings</h5>
							<div class="form-check form-switch custom-switch-v1">
								<input type="checkbox" name="is_enable" class="form-check-input input-primary is_enable" id="is_enable"
									checked="">
								<label class="form-check-label" for="is_enable">Enable</label>
							</div>
						</div>
						<div class="card-body pb-0">
							<div class="row">
								<div class="row referral-settings">
									<div class="col-md-6">
										<div class="form-group">
											<label for="percentage" class="form-label">Commission Percentage (%)</label><span
												class="text-danger">*</span>
											<input class="form-control" placeholder="Enter Commission Percentage" min="0" required="required"
												name="percentage" type="number" value="10" id="percentage">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="minimum_threshold_amount" class="form-label">Minimum Threshold Amount</label><span
												class="text-danger">*</span>
											<div class="input-group">
												<span class="input-group-prepend"><span class="input-group-text">GHS</span></span>
												<input class="form-control" placeholder="Enter Minimum Payout" min="0" required="required"
													name="minimum_threshold_amount" type="number" value="20" id="minimum_threshold_amount">
											</div>
										</div>
									</div>
									<div class="form-group col-12">
										<label for="guideline" class="form-label text-dark">GuideLines</label><span
											class="text-danger">*</span>
										<textarea name="guideline" class="summernote-simple"
											style="display: none;">&lt;p&gt;Earn 10% of the paid amount by who you refer&lt;/p&gt;</textarea>
										<div class="note-editor note-frame card">
											<div class="note-dropzone">
												<div class="note-dropzone-message"></div>
											</div>
											<div class="note-toolbar-wrapper" style="height: 36.5px;">
												<div class="note-toolbar card-header" style="position: relative; top: 0px; width: 100%;">
													<div class="note-btn-group btn-group note-style">
														<div class="note-btn-group btn-group"><button type="button"
																class="note-btn btn btn-light btn-sm dropdown-toggle" tabindex="-1"
																data-bs-toggle="dropdown"><i class="note-icon-magic"></i></button>
															<div class="dropdown-menu dropdown-style"><a class="dropdown-item" href="#"
																	data-value="p">
																	<p>Normal</p>
																</a><a class="dropdown-item" href="#" data-value="blockquote">
																	<blockquote class="blockquote">Blockquote</blockquote>
																</a><a class="dropdown-item" href="#" data-value="pre">
																	<pre>Code</pre>
																</a><a class="dropdown-item" href="#" data-value="h1">
																	<h1>Header 1</h1>
																</a><a class="dropdown-item" href="#" data-value="h2">
																	<h2>Header 2</h2>
																</a><a class="dropdown-item" href="#" data-value="h3">
																	<h3>Header 3</h3>
																</a><a class="dropdown-item" href="#" data-value="h4">
																	<h4>Header 4</h4>
																</a><a class="dropdown-item" href="#" data-value="h5">
																	<h5>Header 5</h5>
																</a><a class="dropdown-item" href="#" data-value="h6">
																	<h6>Header 6</h6>
																</a></div>
														</div>
													</div>
													<div class="note-btn-group btn-group note-font"><button type="button"
															class="note-btn btn btn-light btn-sm note-btn-bold" tabindex="-1"><i
																class="note-icon-bold"></i></button><button type="button"
															class="note-btn btn btn-light btn-sm note-btn-italic" tabindex="-1"><i
																class="note-icon-italic"></i></button><button type="button"
															class="note-btn btn btn-light btn-sm note-btn-underline" tabindex="-1"><i
																class="note-icon-underline"></i></button><button type="button"
															class="note-btn btn btn-light btn-sm note-btn-strikethrough" tabindex="-1"><i
																class="note-icon-strikethrough"></i></button></div>
													<div class="note-btn-group btn-group note-list"><button type="button"
															class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																class="note-icon-unorderedlist"></i></button><button type="button"
															class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																class="note-icon-orderedlist"></i></button>
														<div class="note-btn-group btn-group"><button type="button"
																class="note-btn btn btn-light btn-sm dropdown-toggle" tabindex="-1"
																data-bs-toggle="dropdown"><i class="note-icon-align-left"></i></button>
															<div class="dropdown-menu">
																<div class="note-btn-group btn-group note-align"><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-left"></i></button><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-center"></i></button><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-right"></i></button><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-justify"></i></button></div>
																<div class="note-btn-group btn-group note-list"><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-outdent"></i></button><button type="button"
																		class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																			class="note-icon-align-indent"></i></button></div>
															</div>
														</div>
													</div>
													<div class="note-btn-group btn-group note-insert"><button type="button"
															class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																class="note-icon-link"></i></button><button type="button"
															class="note-btn btn btn-light btn-sm" tabindex="-1"><i
																class="note-icon-chain-broken"></i></button></div>
												</div>
											</div>
											<div class="note-editing-area">
												<div class="note-handle">
													<div class="note-control-selection">
														<div class="note-control-selection-bg"></div>
														<div class="note-control-holder note-control-nw"></div>
														<div class="note-control-holder note-control-ne"></div>
														<div class="note-control-holder note-control-sw"></div>
														<div class="note-control-sizing note-control-se"></div>
														<div class="note-control-selection-info"></div>
													</div>
												</div><textarea class="note-codable"></textarea>
												<div class="note-editable card-block" contenteditable="true" style="height: 468.917px;">
													<p>Earn 10% of the paid amount by who you refer</p>
												</div>
											</div>
											<div class="note-statusbar">
												<div class="note-resizebar">
													<div class="note-icon-bar"></div>
													<div class="note-icon-bar"></div>
													<div class="note-icon-bar"></div>
												</div>
											</div>
											<div class="modal link-dialog" aria-hidden="false" tabindex="-1">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Insert Link</h4> <button type="button" class="btn-close"
																data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															<div class="form-group note-form-group"><label class="note-form-label">Text to
																	display</label><input
																	class="note-link-text form-control note-form-control  note-input" type="text"></div>
															<div class="form-group note-form-group"><label class="note-form-label">To what URL should
																	this link go?</label><input
																	class="note-link-url form-control note-form-control note-input" type="text"
																	value="http://"></div><label class="custom-control custom-checkbox"
																for="sn-checkbox-open-in-new-window"> <input type="checkbox"
																	class="custom-control-input" id="sn-checkbox-open-in-new-window" checked=""> <label
																	class="custom-control-label" for="sn-checkbox-open-in-new-window">Open in new
																	window</label></label>
														</div>
														<div class="modal-footer"><button type="submit" href="#"
																class="btn btn-primary note-btn note-btn-primary note-link-btn" disabled="">Insert
																Link</button></div>
													</div>
												</div>
											</div>
											<div class="modal" aria-hidden="false" tabindex="-1">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Insert Image</h4> <button type="button" class="btn-close"
																data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															<div class="form-group note-form-group note-group-select-from-files"><label
																	class="note-form-label">Select from files</label><input
																	class="note-image-input note-form-control note-input" type="file" name="files"
																	accept="image/*" multiple="multiple"></div>
															<div class="form-group note-group-image-url" style="overflow:auto;"><label
																	class="note-form-label">Image URL</label><input
																	class="note-image-url form-control note-form-control note-input  col-md-12"
																	type="text"></div>
														</div>
														<div class="modal-footer"><button type="submit" href="#"
																class="btn btn-primary note-btn note-btn-primary note-image-btn" disabled="">Insert
																Image</button></div>
													</div>
												</div>
											</div>
											<div class="modal" aria-hidden="false" tabindex="-1">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Insert Video</h4> <button type="button" class="btn-close"
																data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															<div class="form-group note-form-group row-fluid"><label class="note-form-label">Video URL
																	<small class="text-muted">(YouTube, Vimeo, Vine, Instagram, DailyMotion or
																		Youku)</small></label><input
																	class="note-video-url form-control note-form-control note-input" type="text"></div>
														</div>
														<div class="modal-footer"><button type="submit" href="#"
																class="btn btn-primary note-btn note-btn-primary note-video-btn" disabled="">Insert
																Video</button></div>
													</div>
												</div>
											</div>
											<div class="modal" aria-hidden="false" tabindex="-1">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Help</h4> <button type="button" class="btn-close"
																data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body" style="max-height: 300px; overflow: scroll;">
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>ENTER</kbd></label><span>Insert
																Paragraph</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+Z</kbd></label><span>Undoes the last
																command</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+Y</kbd></label><span>Redoes the last
																command</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>TAB</kbd></label><span>Tab</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>SHIFT+TAB</kbd></label><span>Untab</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+B</kbd></label><span>Set a bold
																style</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+I</kbd></label><span>Set a italic
																style</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+U</kbd></label><span>Set a underline
																style</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+S</kbd></label><span>Set a
																strikethrough style</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+BACKSLASH</kbd></label><span>Clean a
																style</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+L</kbd></label><span>Set left
																align</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+E</kbd></label><span>Set
																center align</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+R</kbd></label><span>Set right
																align</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+J</kbd></label><span>Set full
																align</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+NUM7</kbd></label><span>Toggle
																unordered list</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+SHIFT+NUM8</kbd></label><span>Toggle
																ordered list</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+LEFTBRACKET</kbd></label><span>Outdent
																on current paragraph</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+RIGHTBRACKET</kbd></label><span>Indent
																on current paragraph</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM0</kbd></label><span>Change
																current block's format as a paragraph(P tag)</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM1</kbd></label><span>Change
																current block's format as H1</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM2</kbd></label><span>Change
																current block's format as H2</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM3</kbd></label><span>Change
																current block's format as H3</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM4</kbd></label><span>Change
																current block's format as H4</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM5</kbd></label><span>Change
																current block's format as H5</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+NUM6</kbd></label><span>Change
																current block's format as H6</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+ENTER</kbd></label><span>Insert
																horizontal rule</span>
															<div class="help-list-item"></div><label
																style="width: 180px; margin-right: 10px;"><kbd>CTRL+K</kbd></label><span>Show Link
																Dialog</span>
														</div>
														<div class="modal-footer">
															<p class="text-center"><a href="http://summernote.org/" target="_blank">Summernote
																	0.8.9</a> · <a href="https://github.com/summernote/summernote"
																	target="_blank">Project</a> · <a
																	href="https://github.com/summernote/summernote/issues" target="_blank">Issues</a></p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="card-footer text-end">
									<button class="btn-submit btn btn-primary" type="submit">
										Save Changes
									</button>
								</div>

							</div>
						</div>
					</form>
				</div>



			</div>
		</div>
	</div>
</div> --}}

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<!-- Side Navigation -->
			<div class="col-md-3">
				<div class="card sticky-top" style="top: calc(62.4px + 18.2px + 1rem);">
					<div class="list-group list-group-flush" id="useradd-sidenav" role="tablist">
						<!-- Transaction Tab Link -->
						<a href="#transaction-content" class="list-group-item list-group-item-action border-0 active"
							id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction-content" role="tab"
							aria-controls="transaction-content" aria-selected="true">
							Referrals Bonus
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>

						<!-- Payout Request Tab Link -->
						<a href="#payout-request-content" class="list-group-item list-group-item-action border-0"
							id="payout-request-tab" data-bs-toggle="tab" data-bs-target="#payout-request-content" role="tab"
							aria-controls="payout-request-content" aria-selected="false">
							Redemptons
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>

						<!-- Settings Tab Link -->
						<a href="#settings-content" class="list-group-item list-group-item-action border-0" id="settings-tab"
							data-bs-toggle="tab" data-bs-target="#settings-content" role="tab" aria-controls="settings-content"
							aria-selected="false">
							Settings
							<div class="float-end"><i class="ti ti-chevron-right"></i></div>
						</a>
					</div>
				</div>
			</div>

			<!-- Tab Content Area -->
			<div class="col-md-9">
				<div class="tab-content" id="nav-tabContent">
					<!-- Transaction Tab Content -->
					<div class="tab-pane fade show active" id="transaction-content" role="tabpanel" aria-labelledby="transaction-tab">
						@include('referral::admin.referrals._referrals')
					</div>

					<!-- Payout Request Tab Content -->
					<div class="tab-pane fade" id="payout-request-content" role="tabpanel" aria-labelledby="payout-request-tab">
								@include('referral::admin.referrals._redemptions')
					</div>

					<!-- Settings Tab Content -->
					<div class="tab-pane fade" id="settings-content" role="tabpanel" aria-labelledby="settings-tab">
						@include('referral::admin.referrals._settings')
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

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
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endsection


@section('page-script')

<script>
	$(document).ready(function () {
				"use strict"
				
				// init table dom
				let Table = $("table[id=downliners-table]");
				let RedemptionsTable = $("table[id=redemptions-table]");

				// Initialize CKEditor on the guidelines textarea
				ClassicEditor.create(document.querySelector('#guideline'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                'blockQuote', 'insertTable', 'undo', 'redo'
            ]
        })
        .catch(error => {
            console.error(error);
        });
				
				// init list view datatable
				let dataListView = $('.datatables-basic-referrals').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.admin.referrals.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
								{"data": 'responsive_id', orderable: false, searchable: false},
								{"data": "uid"},
								{"data": "uid"},
								{"data": "name"},
								{"data": "earned_bonus", orderable: true, searchable: false},
								{"data": "balance", orderable: true, searchable: false},
								{"data": "status", orderable: false, searchable: false},
								{"data": "created_at", visible: false},
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
														$name = full['isAdmin'] || full['super_user'] ? full['name'] : full['user_id'],
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
														'<small class="emp_name text-truncate fw-bold">' +
														$name +
														'</small>' +
														'<small class="emp_post text-truncate text-muted">' +
														$created_at +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										// Status
										targets: 6,
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
						order: [[7, "desc"]],
						displayLength: 10,
				});
				
				// init list view datatable
				let redemptionsDataListView = $('.datatables-basic-redemptions').DataTable({
						"processing": true,
						"serverSide": true,
						"ajax": {
								"url": "{{ route('referral.admin.redemptions.search') }}",
								"dataType": "json",
								"type": "POST",
								"data": {_token: "{{csrf_token()}}"}
						},
						"columns": [
							{"data": 'responsive_id', orderable: false, searchable: false},
							{"data": "uid"},
							// {"data": "request_id"},
							{"data": "recipient", orderable: false, searchable: false},
							{"data": "amount"},
							{"data": "moneytary_value"},
							{"data": "payout_method"},
							{"data": "status"},
							{"data": "created_at", visible: false},
							{"data": "actions", orderable: false, searchable: false}
						],
						searchDelay: 500,
						columnDefs: [
								{
										// For Responsive
										targets: 0,
										className: 'control',
										orderable: false,
										responsivePriority: 2
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
														$isAdmin = full['isAdmin'],
														$name = $isAdmin ? full['name'] : full['user_id'],
														$created_at = full['created_at']
													
												if ($user_img) {
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
														'<small class="emp_name text-truncate fw-bold text-sm">' +
														$name +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										targets: 3,
										responsivePriority: 2,
										render: function (data, type, full) {
												var $created_at = full['created_at'],
														$payout_method = full['actual_payout_method'],
														$amount = $payout_method == "{{ \Tonkra\Referral\Models\ReferralRedemption::PAYOUT_WALLET }}"
																			? '<span class="text-muted text-truncate text-decoration-line-through">' + full['amount'] + '</span>' 
																			: '<span class="emp_name text-truncate fw-bold">' + full['amount'] + '</span>'

												// Creates full output for row
												return '<div class="d-flex justify-content-left align-items-center">' +
														'<div class="d-flex flex-column">' +
														$amount +
														'<small class="emp_post text-truncate text-muted">' +
														$created_at +
														'</small>' +
														'</div>' +
														'</div>';
										}
								},
								{
										targets: 4,
										responsivePriority: 3,
										render: function (data, type, full) {
												var $payout_method = full['actual_payout_method'],
														$moneytary_value = $payout_method == "{{ \Tonkra\Referral\Models\ReferralRedemption::PAYOUT_SMS }}"
																			? '<span class="text-muted text-truncate text-decoration-line-through">' + full['moneytary_value'] + '</span>' 
																			: '<span class="emp_name text-truncate fw-bold">' + full['moneytary_value'] + '</span>'

												return $moneytary_value 
										}
								},
								{
										// Status
										targets: 5,
										responsivePriority: 4,
								},
								{
										// Actions
										targets: -1,
										title: '{{ __('locale.labels.actions') }}',
										orderable: false,
										render: function (data, type, full) {
											let actions = '',
													$actual_status = full['actual_status'],
													$status = {label: '', btn_color: '', btn_border: '', status: ''},
													$status_dropdown_list = '';
											
											if(full['actual_status'] != "{{ \Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}"){
												$status.label = "{{ __('referral::locale.referral_redemptions.complete') }}"
												$status.btn_color = "btn-success"
												$status.btn_border = "btn-outline-success"
												$status.status = "{{ \Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}"
											}
											
											full['status_dropdown_list'].forEach(el => {
												if(full['actual_status'] == el){
													return;
												}
												$status_dropdown_list += `<li><a class="dropdown-item action-change-status text-capitalize" data-id="${full['uid']}"
														data-new-status="${el}"> ${el.replace(/_/g, ' ')} </a></li>`
											});

											if($actual_status != '{{ Tonkra\Referral\Models\ReferralRedemption::STATUS_COMPLETED }}'){
												actions += `<div class="btn-group">
																			<button class="btn btn-sm ${$status.btn_color} ${$status.btn_border} action-change-status" type="button"
																				data-id="${full['uid']}" data-new-status="${$status.status}">
																				${$status.label}
																			</button>
																			<button type="button" class="btn btn-sm btn-default ${$status.btn_border}  dropdown-toggle dropdown-toggle-split"
																				data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('locale.labels.change_status') }}">
																				<span class="visually-hidden">Toggle Dropdown</span>
																			</button>
																			<ul class="dropdown-menu">
																				${$status_dropdown_list}
																			</ul>
																		</div>`;
											}
                           
                            // actions += `
                            //     <div class="btn-group">
                            //         <button class="btn btn-sm ${$status.btn_color} ${$status.btn_border} action-change-status" type="button" data-id="${full['uid']}" data-new-status="${$status.status}">
                            //             ${$status.label}
                            //         </button>
                            //         <button type="button" class="btn btn-sm btn-default ${$status.btn_border}  dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('locale.labels.change_status') }}">
                            //             <span class="visually-hidden">Toggle Dropdown</span>
                            //         </button>
                            //         <ul class="dropdown-menu">
                            //             ${$status_dropdown_list}
                            //         </ul>
                            //     </div>`;

                            // actions += `
                            //     <div class="btn-group">
                            //         <button class="btn btn-default btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            //             <i class="fas fa-ellipsis-v"></i>
                            //         </button>
                            //         <ul class="dropdown-menu p-1">
                            //             <li class="mb-1">
                            //                 <a href="${full['edit']}" class="btn btn-sm btn-primary d-block w-100 cursor-pointer">
                            //                     <i class="fas fa-edit"></i> {{ __('locale.buttons.edit') }}
                            //                 </a>
                            //             </li>
                            //             <li>
                            //                 <a class="btn btn-sm action-delete btn-danger d-block w-100 cursor-pointer" data-id="${full['uid']}">
                            //                     <i class="fas fa-trash"></i>  {{ __('locale.buttons.delete') }}
                            //                 </a>
                            //             </li>
                            //         </ul>
                            //     </div>`;
                                
                            return actions;
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
													console.log(col);
													
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
						aLengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
						select: {
								style: "multi"
						},
						order: [[7, "desc"]],
						displayLength: 10,
				});

				// On Change Status
				$("body").on('click', 'button.action-change-status, a.action-change-status', function (e) {
						e.stopPropagation();
						let id = $(this).data('id'), new_status = $(this).data('new-status'), $button = $(this);
						
						Swal.fire({
								title: "{{ __('locale.labels.are_you_sure') }}",
								text: "{{ __('locale.labels.able_to_revert') }}",
								icon: 'warning',
								showCancelButton: true,
								confirmButtonText: "{{ __('locale.labels.yes') }}",
								customClass: {
										confirmButton: 'btn btn-primary',
										cancelButton: 'btn btn-outline-danger ms-1'
								},
								buttonsStyling: false,
						}).then(function (result) {
								if (result.value) {
										$button.prepend('<i class="fas fa-spinner fa-spin me-2"></i>');
										$button.prop('disabled', true);

										$.ajax({
												url: "{{ url(config('referral.admin_path').'/redemptions')}}" + '/' + id + '/update-status',
												type: "PUT",
												data: {
														_token: "{{csrf_token()}}",
														status: new_status
												},
												success: function (data) {
														$button.find('i').remove();
														$button.prop('disabled', false);
														// showToast(data);
														location.reload();

												},
												error: function (reject) {
														$button.find('i').remove();
														$button.prop('disabled', false);
														if (reject.status === 422) {
																let errors = reject.responseJSON.errors;
																$.each(errors, function (key, value) {
																		toastr['warning'](value[0], "{{__('locale.labels.attention')}}", {
																				closeButton: true,
																				positionClass: 'toast-top-right',
																				progressBar: true,
																				newestOnTop: true,
																				rtl: isRtl
																		});
																});
														} else {
																toastr['warning'](reject.responseJSON.message, "{{__('locale.labels.attention')}}", {
																		positionClass: 'toast-top-right',
																		containerId: 'toast-top-right',
																		progressBar: true,
																		closeButton: true,
																		newestOnTop: true
																});
														}
												},
												complete: function(data){
														$button.find('i').remove();
														$button.prop('disabled', false);
														$button.closest('.modal').modal('hide');
												}
										})
								}
						})
				});

				// $("body").on('click', '.copy', function (e) {
				// 	e.preventDefault()
				// 	copyToClipboard($(this).data('text'));
				// })
			});
</script>
@endsection