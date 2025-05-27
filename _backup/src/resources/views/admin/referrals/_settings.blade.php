@php use Tonkra\Referral\Helpers\Helper; @endphp

<div class="card border-0 shadow-sm">
	<form class="needs-validation" novalidate action="{{ route('referral.admin.setting.store') }}" method="post">
		@csrf
		<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-1">
			<h4 class="mb-0 ">Referral Program Settings</h4>
			<div class="form-check form-switch mb-0">
				<input class="form-check-input" type="checkbox" role="switch" id="status" name="status"
					@checked((bool)config('referral.status'))>
				<label class="form-check-label" for="status">
					{{ __('referral::locale.labels.status') }}
				</label>
			</div>
		</div>

		<div class="card-body">
			<div class="row g-2">
				<!-- Main Settings -->
				<div class="col-md-6 mb-1">
					<label for="bonus" class="form-label small mb-0 required">
						{{ __('referral::locale.referrals.bonus') }} (%)
					</label>
					<input type="number" class="form-control form-control" id="bonus" name="bonus" min="0" required
						value="{{ config('referral.bonus') }}">
					@error('bonus')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>

				<div class="col-md-6 mb-1">
					<label for="redemption_rate" class="form-label small mb-0 required">
						{{ __('referral::locale.referral_bonuses.redemption_rate') }}
					</label>
					<input type="number" class="form-control form-control" id="redemption_rate" name="redemption_rate"
						step="0.001" min="0.001" value="{{ config('referral.redeem.rate') }}">
					@error('redemption_rate')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>

				<div class="col-12 mb-1">
					<label for="default_senderid" class="form-label small mb-0">
						{{ __('referral::locale.referrals.default_senderid') }}
					</label>
					<input type="text" class="form-control form-control" id="default_senderid" name="default_senderid"
						value="{{ config('referral.default_senderid') }}">
					@error('default_senderid')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>

				<div class="col-md-6 mb-1">
					<label for="email_notification" class="form-label small mb-0 required">
						{{ __('referral::locale.referrals.email_notification') }}
					</label>
					<select class="form-select form-select" id="email_notification" name="email_notification">
						<option value="1" @selected((bool)config('referral.email_notification'))>Enabled</option>
						<option value="0" @selected(!(bool)config('referral.email_notification'))>Disabled</option>
					</select>
					@error('email_notification')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>

				<div class="col-md-6 mb-1">
					<label for="sms_notification" class="form-label small mb-0 required">
						{{ __('referral::locale.referrals.sms_notification') }}
					</label>
					<select class="form-select form-select" id="sms_notification" name="sms_notification">
						<option value="1" @selected((bool)config('referral.sms_notification'))>Enabled</option>
						<option value="0" @selected(!(bool)config('referral.sms_notification'))>Disabled</option>
					</select>
					@error('sms_notification')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>

				<!-- Minimum Redemption Amounts -->
				<div class="col-12 mt-2">
					<h6 class="small fw-semibold text-muted mb-1">MINIMUM REDEMPTION AMOUNTS</h6>
				</div>

				<!-- SMS Redemption -->
				<div class="col-md-6 mb-1">
					<label class="required" for="min_sms_redemption_status">{{ __('referral::locale.referral_bonuses.minimum_sms_redemption_status') }}</label>
					<div class="d-flex align-items-center gap-2">
						<div class="form-check form-switch mb-0 flex-shrink-0">
							<input class="form-check-input" type="checkbox" role="switch" id="min_sms_redemption_status"
								name="min_sms_redemption_status" @checked((bool)config('referral.redeem.types.sms_unit.status'))>
						</div>
						<div class="flex-grow-1">
							<input type="number" class="form-control form-control" id="min_sms_redemption_amount"
								name="min_sms_redemption_amount" step="1" min="1" placeholder="SMS Amount"
								value="{{ config('referral.redeem.types.sms_unit.min_amount') }}">
							@error('min_sms_redemption_amount')
							<div class="invalid-feedback d-block small">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<!-- Withdrawal Redemption -->
				<div class="col-md-6 mb-1">
					<label class="required" for="min_sms_redemption_status">{{ __('referral::locale.referral_bonuses.minimum_withdrawal_redemption_status') }}</label>
					<div class="d-flex align-items-center gap-2">
						<div class="form-check form-switch mb-0 flex-shrink-0">
							<input class="form-check-input" type="checkbox" role="switch" id="min_withdrawal_redemption_status"
								name="min_withdrawal_redemption_status"
								@checked((bool)config('referral.redeem.types.withdrawal.status'))>
						</div>
						<div class="flex-grow-1">
							<input type="number" class="form-control form-control" id="min_withdrawal_redemption_amount"
								name="min_withdrawal_redemption_amount" step="1" min="1" placeholder="Withdrawal Amount"
								value="{{ config('referral.redeem.types.withdrawal.min_amount') }}">
							@error('min_withdrawal_redemption_amount')
							<div class="invalid-feedback d-block small">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<!-- Transfer Redemption -->
				<div class="col-md-6 mb-1">
					<label class="required" for="min_sms_redemption_status">{{ __('referral::locale.referral_bonuses.minimum_transfer_redemption_status') }}</label>
					<div class="d-flex align-items-center gap-2">
						<div class="form-check form-switch mb-0 flex-shrink-0">
							<input class="form-check-input" type="checkbox" role="switch" id="min_transfer_redemption_status"
								name="min_transfer_redemption_status" @checked((bool)config('referral.redeem.types.transfer.status'))>
						</div>
						<div class="flex-grow-1">
							<input type="number" class="form-control form-control" id="min_transfer_redemption_amount"
								name="min_transfer_redemption_amount" step="1" min="1" placeholder="Transfer Amount"
								value="{{ config('referral.redeem.types.transfer.min_amount') }}">
							@error('min_transfer_redemption_amount')
							<div class="invalid-feedback d-block small">{{ $message }}</div>
							@enderror
						</div>
					</div>
				</div>

				<!-- Guidelines -->
				<div class="col-12 mt-2 mb-1">
					<label for="guideline" class="form-label small fw-semibold mb-0">GUIDELINES</label>
					<textarea class="form-control form-control" id="guideline" name="guideline"
						rows="3">{{ old('guideline', Helper::desanitizeFromEnv(config('referral.guidelines'))) }}</textarea>
					@error('guideline')
					<div class="invalid-feedback d-block small">{{ $message }}</div>
					@enderror
				</div>
			</div>
		</div>

		<div class="card-footer bg-white border-top-0 text-end p-3">
			<button type="submit" class="btn btn-sm btn-primary px-3">
				<i class="fas fa-save me-1"></i> Save Changes
			</button>
		</div>
	</form>
</div>