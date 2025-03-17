@component('mail::message')

{!! $content !!}

@component('mail::button', ['url' => $url])
{{ __('referral::locale.labels.view') }}
@endcomponent

{{ __('referral::locale.labels.thanks') }},<br>
{{ config('app.name') }}
@endcomponent
