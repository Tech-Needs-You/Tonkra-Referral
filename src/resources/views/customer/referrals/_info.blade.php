<div class="row p-1">	
	<div class="card col-md-3">
			<div class="card-head pt-1">
					<small>
						<ul class="nav nav-tabs nav-justified" role="tablist">
							<li class="nav-item">
									<a class="nav-link text-sm active" id="info-personal-tab" data-bs-toggle="tab" aria-controls="info-personal" href="#info-personal" role="tab" aria-selected="true">
										<i class="fa fa-user"></i> {{ __('referral::locale.referrals.personal_info') }}
									</a>
							</li>

							<li class="nav-item">
									<a class="nav-link" id="info-upliner-tab" data-bs-toggle="tab" aria-controls="info-upliner" href="#info-upliner" role="tab" aria-selected="false"> 
										<i class="fa fa-hand-holding-heart"></i> {{__('referral::locale.referrals.upliner_info')}} 
									</a>
							</li>
						</ul>
					</small>
			</div>
			<div class="card-body row my-25">
					<div class="tab-content">
							<div class="tab-pane fade  show active" id="info-personal" role="tabpanel" aria-labelledby="info-personal-tab">
								<div class="d-flex justify-content-center">
									<div class="d-flex flex-column justify-content-center align-items-center">
										<a nohref="{{ route('user.account') }}" class="me-25 mb-1">
												<img src="{{ route('user.avatar') }}" alt="{{ ucwords($user->displayName()) }}"
														class="uploadedAvatar rounded-circle img-thumbnail me-50"
														height="150" width="150"
												/>
										</a>
										<span class="mb-1"><b><i data-feather="user-check"></i>&nbsp;{{ ucwords($user->displayName()) }}</b></span>

										<label class="mb-1 font-weight-bold"><a class="text-info" href="mailto:{{ $user->email }}">{{ $user->email }}</a></label>

										<label class="mb-1">
											<i data-feather="key"></i>&nbsp;
											<i class="text-truncate">{{ $user->uid }}</i> 
											<button class="copy-to-clipboard btn btn-sm btn-outline-primary" data-text="{{ $user->uid }}"  data-refferal-response="{{ __('referral::locale.labels.referral_code_copied') }}" title="{{__('referral::locale.buttons.copy_referral_code')}}">
												<span class="font-weight-bold" style="font-size: 12px"><i data-feather="copy"></i></span>
											</button>
										</label>
									</div>
								</div>

								<div class="d-flex justify-content-center mb-1">
										<button class="copy-to-clipboard btn btn-sm btn-outline-info" data-text="{{ $user->referralLink() }}"  data-refferal-response="{{ __('referral::locale.labels.referral_link_copied') }}" title="{{__('referral::locale.buttons.copy_referral_link')}}">
											<span class="font-weight-bold" style="font-size: 12px"><i class="fa fa-link"></i> {{__('referral::locale.buttons.copy_referral_link')}}</span>
										</button>
								</div>
							</div>

							<div class="tab-pane fade show" id="info-upliner" role="tabpanel" aria-labelledby="info-upliner-tab">
									@if (! is_null($referrer))

										<div class="d-flex justify-content-center">
											<div class="d-flex flex-column justify-content-center align-items-center">
												<a nohref="{{ route('user.account') }}" class="me-25 mb-1">
														<img src="{{ route('referral.user.referrer_avatar') }}" alt="{{ ucwords($referrer->displayName()) }}"
																class="uploadedAvatar rounded-circle img-thumbnail me-50"
																height="150" width="150"
														/>
												</a>
												<span class="mb-1"><b><i data-feather="user-check"></i>&nbsp;{{ ucwords($referrer->displayName()) }}</b></span>
												<label class="mb-1 text-muted"><i data-feather="key"></i
													>&nbsp;{{ $referrer->uid }} 
													<button class="copy-to-clipboard btn btn-sm btn-outline-primary" data-text="{{ $referrer->uid }}"  data-refferal-response="{{ __('referral::locale.labels.upliner_code_copied') }}" title="{{__('referral::locale.buttons.copy_upliner_code')}}">
													<span class="font-weight-bold" style="font-size: 12px"><i data-feather="copy"></i></span>
												</button></label>
												<label class="mb-1 font-weight-bold"><a class="text-info" href="mailto:{{ $referrer->email }}">{{ $referrer->email }}</a></label>
											</div>
										</div>

										<div class="d-flex justify-content-center">
											<span>
													<a  no-href class="btn btn-sm border1 border-primary text-primary" title="{{ str_plural(__('referral::locale.referrals.downliners_count', ['count' => $referrer_downline_count]), $referrer_downline_count) }}"> 
														<i class="fas fa-hashtag fa-1x text-primary"> </i> {{ str_plural(__('referral::locale.referrals.downliners_count', ['count' => $referrer_downline_count]), $referrer_downline_count) }}
													</a>
											</span>
										</div>
									@else
										<div class="card-body row my-25">
						
												<div class="d-flex justify-content-center">
						
													<!-- header section -->
													<div class="d-flex flex-column justify-content-center align-items-center">
														<i class="fas fa-user-slash fa-9x text-muted"></i>
														<span class="mb-1"><b>{{ __('referral::locale.referrals.no_upliner_set') }}</b></span>
													</div>
													<!--/ header section -->
												</div>
										</div>
									@endif
							</div>
					</div>
					
				</div>
	</div>


	<div class="card col-md-9">
		<div class="card-head mt-1 d-flex justify-content-between">
				<small><i class="fa fa-heart-pulse"></i> {{ __('referral::locale.labels.my_downlines') }}</small>
				{{-- <span>
					<button class="copy-to-clipboard btn btn-info btn-sm" role="button" data-text="{{ $user->uid }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('referral::locale.buttons.copy_referral_code') }}"><i class="text-white fas fa-copy"></i></button>
					<button class="copy-to-clipboard btn btn-default btn-outline-primary btn-sm" role="button" data-text="{{ route('register_with_referrer', $user->uid )}}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('referral::locale.buttons.copy_referral_link') }}"><i class="text-primary fas fa-share-nodes"></i></button>
				</span> --}}
		</div>
		<div class="card-body mb-25">
				<!-- Basic table -->
				<section id="datatables-basic1">
					<div class="mb-1 mt-1">
						<div class="btn-group">
							<button class="btn btn-primary btn-sm fw-bold dropdown-toggle" type="button" id="bulk_actions" data-bs-toggle="dropdown" aria-expanded="false">
									{{ __('referral::locale.labels.actions') }}
							</button>
							<div class="dropdown-menu" aria-labelledby="bulk_actions">
									<a class="dropdown-item bulk-top_up" href="#" onclick="alert('Under Construction...')"><i data-feather="trending-up" class="text-success">&nbsp;</i> {{ __('referral::locale.datatables.bulk_top_up') }}</a>
									<a class="dropdown-item bulk-flag" href="#" onclick="alert('Under Construction...')"><i data-feather="flag" class="text-danger">&nbsp;</i> {{ __('referral::locale.datatables.bulk_flag') }}</a>
							</div>
						</div>
	
						{{-- <div class="btn-group">
								<a href="{{route('admin.customers.export')}}" class="btn btn-info btn-sm waves-light waves-effect fw-bold"> {{__('referral::locale.buttons.export')}} <i data-feather="file-text"></i></a>
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