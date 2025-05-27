<div class="row">
    <div class="col-12 me-1">
        <div class="card">
            <div class="card-body">
                <form class="form" action="{{ route('referral.customer.preferences', \Tonkra\Referral\Models\UserPreference::KEY_REFERRAL) }}" method="post">
                    @csrf

                    <div class="row col-md-6">											
											<div class="col-12 mb-1 ">
													<label for="status" class="form-label required">{{__('referral::locale.referrals.activate_referral')}}</label>

													<div class="form-check form-switch">
														<input class="form-check-input checkbox-lg text-uppercase" type="checkbox" id="status" name="status" value="1" onchange="this.nextElementSibling.textContent = this.checked ? '{{ __('referral::locale.referrals.activated') }}' : '{{ __('referral::locale.referrals.deactivated') }}';"
															{{ (bool)$referral_preference?->get('status') ? 'checked' : '' }} />

														<label class="form-check-label text-uppercase" for="status">
															{{ (bool)$referral_preference?->get('status') ? __('referral::locale.referrals.activated') : __('referral::locale.referrals.deactivated') }}
														</label>
													</div>
													<small class="text-danger px-1 fw-bold "><i class="fa fa-info-circle"></i> {!! __('referral::locale.referrals.activate_referral_note') !!}</small>

													@error('email_referral_notification')
													<p><small class="text-danger">{{ $message }}</small></p>
													@enderror
											</div>

											<div class="col-12 mb-1">
													<div class=""> 
															<label for="email_referral_notification" class="form-label required">{{__('referral::locale.referrals.receive_email_referral_notification')}}</label>
															<select class="form-select" id="email_referral_notification" name="email_notification" required>
																	<option value="" @if($referral_preference?->get('email_notification') === null) selected @endif readonly> {{ __('referral::locale.labels.select') }} </option>
																	<option value="1" @if($referral_preference?->get('email_notification') !== null && (bool)$referral_preference?->get('email_notification') == true ) selected @endif>{{ __('referral::locale.labels.yes')}}</option>
																	<option value="0" @if($referral_preference?->get('email_notification') !== null && (bool)$referral_preference?->get('email_notification') === false) selected @endif>{{ __('referral::locale.labels.no')}}</option>
															</select>
													</div>
													@error('email_referral_notification')
													<p><small class="text-danger">{{ $message }}</small></p>
													@enderror
											</div>
											
											<div class="col-12">
													<div class="mb-1">
															<label for="sms_referral_notification" class="form-label required">{{__('referral::locale.referrals.receive_sms_referral_notification')}}</label>
															<select class="form-select" id="sms_referral_notification" name="sms_notification" required>
																<option value="" @if($referral_preference?->get('sms_notification') === null) selected @endif> {{ __('referral::locale.labels.select') }} </option>
																	<option value="1" @if($referral_preference?->get('sms_notification') !== null && (bool)$referral_preference?->get('sms_notification') == true ) selected @endif>{{ __('referral::locale.labels.yes')}}</option>
																	<option value="0" @if($referral_preference?->get('sms_notification') !== null && (bool)$referral_preference?->get('sms_notification') === false) selected @endif>{{ __('referral::locale.labels.no')}}</option>
															</select>
													</div>
													@error('sms_referral_notification')
													<p><small class="text-danger">{{ $message }}</small></p>
													@enderror
											</div>
											
											<div class="col-12 d-flex flex-sm-row flex-column justify-content-end mt-1">
													<button type="submit" class="btn btn-primary"><i data-feather="save"></i> {{__('referral::locale.buttons.save')}}</button>
											</div>
										</div>
                </form>
            </div>
        </div>
    </div>
</div>
