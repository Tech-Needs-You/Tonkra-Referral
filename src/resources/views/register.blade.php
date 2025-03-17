@extends('layouts/fullLayoutMaster')

@section('title', __('referral::locale.auth.register'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/wizard/bs-stepper.min.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('page-style')
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-wizard.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">
  {{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/base/pages/page-pricing.css') }}"> --}}

  @if (config('no-captcha.registration'))
    {!! RecaptchaV3::initJs() !!}
  @endif
@endsection

@section('content')
  <div class="auth-wrapper auth-cover">
    <div class="auth-inner row m-0">
      <!-- Brand logo-->
      <a class="brand-logo" href="{{ route('login') }}">
        <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name') }}" />
      </a>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <div class="col-lg-3 d-none d-lg-flex align-items-center p-0">
        <div class="w-100 d-lg-flex align-items-center justify-content-center">
          <img class="img-fluid w-100" src="{{ asset('images/pages/create-account.svg') }}"
            alt="{{ config('app.name') }}" />
        </div>
      </div>
      <!-- /Left Text-->

      <!-- Register-->
      <div class="col-lg-9 d-flex align-items-center auth-bg px-2 px-sm-3 px-lg-5 pt-3">
        <div class="width-700 mx-auto">
          <div class="bs-stepper register-multi-steps-wizard shadow-none">
            <div class="bs-stepper-header px-0 d-flex justify-content-around" role="tablist">

              <div class="step" data-target="#account-details" role="tab" id="account-details-trigger">
                <button type="button" class="step-trigger">
                  <span class="bs-stepper-box">
                    <i data-feather="user" class="font-medium-3"></i>
                  </span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">{{ __('referral::locale.labels.account') }}</span>
                    <span class="bs-stepper-subtitle">{{ __('referral::locale.auth.enter_credentials') }}</span>
                  </span>
                </button>
              </div>


            </div>

            <div class="bs-stepper-content px-0 mt-3">

              @if ($errors->any())

                @foreach ($errors->all() as $error)
                  <div class="alert alert-danger" role="alert">
                    <div class="alert-body">{{ $error }}</div>
                  </div>
                @endforeach

              @endif

              @php
                $form_action = route('referral.register.post');
                if ($with_referrer) {
                    $form_action = route('referral.register.with_referrer.post', $referrer);
                }
              @endphp

              <form method="POST" action="{{ $form_action }}">
                @csrf
                <div id="account-details" class="content get_form_data" role="tabpanel"
                  aria-labelledby="account-details-trigger">
                  <div class="content-header mb-2">
                    <h2 class="fw-bolder mb-75">{{ __('referral::locale.auth.account_information') }}</h2>
                    <span>{{ __('referral::locale.auth.fill_form_to_create_account') }}</span>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-1">
                      <label class="form-label required" for="first_name">{{ __('referral::locale.labels.first_name') }}</label>
                      <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror"
                        name="first_name" placeholder="{{ __('referral::locale.labels.first_name') }}"
                        value="{{ old('first_name') }}" required autocomplete="first_name" />

                      @error('first_name')
                        <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label required" for="last_name">{{ __('referral::locale.labels.last_name') }}</label>
                      <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror"
                        name="last_name" placeholder="{{ __('referral::locale.labels.last_name') }}"
                        value="{{ old('last_name') }}" autocomplete="last_name" />

                      @error('last_name')
                        <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label required" for="email">{{ __('referral::locale.labels.email') }}</label>
                      <input type="email" id="email"
                        class="form-control required @error('email') is-invalid @enderror" value="{{ old('email') }}"
                        name="email" placeholder="{{ __('referral::locale.labels.email_address') }}" required />

                      @error('email')
                        <div class="invalid-feedback">
                          {{ $message }}
                        </div>
                      @enderror
                    </div>

                    <div class="col-md-6 mb-1">
                      <div class="col-12">
                        <div class="mb-1">
                          <label for="phone" class="form-label required">{{ __('referral::locale.labels.phone') }}</label>
                          <div class="input-group">
                            <div style="width: 8rem">
                              <select class="form-select select2" name="country_code" id="country_code" required>
                                @foreach (Helper::countries() as $country)
                                  <option value="{{ $country['d_code'] }}"
                                    {{ strtolower(config('app.country')) == strtolower($country['name']) ? 'selected' : null }}>
                                    {{ $country['code'] }}({{ $country['d_code'] }})</option>
                                @endforeach
                              </select>
                            </div>

                            <input type="text" id="phone"
                              class="form-control @error('phone') is-invalid @enderror"
                              value="{{ old('phone', $phone ?? null) }}" name="phone" required
                              placeholder="{{ __('referral::locale.labels.phone') }}">
                          </div>

                          @error('phone')
                            <p><small class="text-danger">{{ $message }}</small></p>
                          @enderror
                          @error('country_code')
                            <p><small class="text-danger">{{ $message }}</small></p>
                          @enderror
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label required" for="password">{{ __('referral::locale.labels.password') }}</label>
                      <div class="input-group input-group-merge form-password-toggle">
                        <input type="password" id="password"
                          class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}"
                          name="password" required />
                        <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                      </div>

                      @error('password')
                        <div class="invalid-feedback">
                          {{ $message }}
                        </div>
                      @enderror
                    </div>

                    <div class="col-md-6 mb-1">
                      <label class="form-label required"
                        for="password_confirmation">{{ __('referral::locale.labels.password_confirmation') }}</label>
                      <div class="input-group input-group-merge form-password-toggle">
                        <input type="password" id="password_confirmation"
                          class="form-control @error('password_confirmation') is-invalid @enderror"
                          value="{{ old('password_confirmation') }}" name="password_confirmation" required />
                        <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                      </div>
                    </div>
                  </div>

                  @php
                    $readonly = '';
                    $text_color = '';
                    if (!is_null($referrer)) {
                        $readonly = 'readonly';
                        $text_color = 'text-muted';
                    }
                  @endphp

                  @if ($isReferralEnabled)
                    <div class="col-12 mb-1">
                      <label class="form-label" for="referrer">{{ __('referral::locale.labels.referrer') }}</label>
                      <input type="text" id="referrer"
                        class="form-control @error('referrer') is-invalid @enderror {{ $text_color }}"
                        name="referrer" placeholder="{{ __('referral::locale.labels.referral_code') }}"
                        value="{{ old('referrer') ?? $referrer }}" {{ $readonly }}>
                      @error('referrer')
                        <div class="invalid-feedback">
                          <strong>{{ $message }}</strong>
                        </div>
                      @enderror
                    </div>
                  @endif

                  <div>
                    <div class="mb-1">
                      @if (config('no-captcha.registration'))
                        <fieldset class="form-label-group position-relative">
                          {!! RecaptchaV3::field('register') !!}
                        </fieldset>
                      @endif

                      @if (config('no-captcha.registration'))
                        @error('g-recaptcha-response')
                          <span class="text-danger">{{ __('referral::locale.labels.g-recaptcha-response') }}</span>
                        @enderror
                      @endif
                    </div>
                  </div>
                  <div class="d-flex justify-content-between mt-2 align-items-between">
                    <a href="{{ url('login') }}">
                      <i data-feather="chevron-left"></i> {{ __('referral::locale.auth.back_to_login') }}
                    </a>
                    <!-- Registration form submit button -->
                    <button class="btn btn-success btn-submit" type="submit">
                      <i data-feather="check" class="align-middle me-sm-25 me-0"></i>
                      <span class="align-middle d-sm-inline-block">{{ __('referral::locale.buttons.submit') }}</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('vendor-script')
  <script src="{{ asset(mix('vendors/js/forms/wizard/bs-stepper.min.js')) }}"></script>
  <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')

  <script>
    let registerMultiStepsWizard = document.querySelector('.register-multi-steps-wizard'),
      pageResetForm = $('.auth-register-form'),
      numberedStepper,
      priceOption = $('.pricing-data'),
      payment_method = $('#payment_method'),
      select = $('.select2'),
      selectedPlan = $('input[name="plans"]:checked');

    $(function() {
      //make payment options required by default
      payment_method.attr('required', '');

      if (selectedPlan.data('price') == '0.00') {
        $('.hide-for-free').hide();
      }
    })

    priceOption.delegate(".planPrice", "click", function(e) {
      e.stopPropagation();
      if ($(this).data('value') === '0.00') {
        $('.hide-for-free').hide();
        payment_method.removeAttr('required');
      } else {
        $('.hide-for-free').show();
        payment_method.attr('required', '');
      }
    });

    // multi-steps registration
    // --------------------------------------------------------------------

    // Horizontal Wizard
    if (typeof registerMultiStepsWizard !== undefined && registerMultiStepsWizard !== null) {
      numberedStepper = new Stepper(registerMultiStepsWizard);

      $(registerMultiStepsWizard)
        .find('.btn-next')
        .each(function() {
          $(this).on('click', function(e) {

            let email = $('#email').val().length,
              first_name = $('#first_name').val().length,
              last_name = $('#last_name').val().length,
              password = $('#password').val(),
              confirm_password = $('#password_confirmation').val();

            if (first_name !== 0 && last_name !== 0 && email !== 0 && password.length !== 0 && password ===
              confirm_password) {
              numberedStepper.next();
            } else if (password !== confirm_password) {

              e.preventDefault();

              toastr['error']("{{ __('referral::locale.customer.both_password_not_matched') }}", 'Oops..!!', {
                closeButton: true,
                positionClass: 'toast-top-right',
                progressBar: true,
                newestOnTop: true,
                rtl: isRtl
              });
            } else {
              e.preventDefault();

              toastr['error']("{{ __('referral::locale.auth.insert_required_fields') }}", 'Oops..!!', {
                closeButton: true,
                positionClass: 'toast-top-right',
                progressBar: true,
                newestOnTop: true,
                rtl: isRtl
              });
            }
          });
        });

      $(registerMultiStepsWizard)
        .find('.btn-prev')
        .on('click', function() {
          numberedStepper.previous();
        });
    }

    // select2
    select.each(function() {
      let $this = $(this);
      $this.wrap('<div class="position-relative"></div>');
      $this.select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        dropdownAutoWidth: true,
        width: '100%',
        dropdownParent: $this.parent()
      });
    });

    sanitizePhone($('input[id=phone]'))

    $('input[id=phone]').on('change keyup paste', function() {
      sanitizePhone($(this))
    });

    function sanitizePhone(element) {
      let $phone = element.val(),
        $submitBtn = element.closest('form').find(':submit');
      const regex = new RegExp("^0+(?!$)", 'g')

      $submitBtn.prop('disabled', true)
      //remove non-numeric characters
      if ($phone.length > 0 && !$phone.match(/^\d+$/)) {
        showToast("info", "{{ __('referral::locale.labels.only_numbers') }}")
        element.val($phone.replace(/\D/g, ''));
      }

      // prevent leading zeros
      if ($phone.length > 1 && $phone.match(regex)) {
        showToast("info", "{{ __('referral::locale.labels.no_leading_zeros') }}")
        element.val($phone.replaceAll(regex, ""));
      }
      $submitBtn.prop('disabled', false)
    }
  </script>
@endsection
