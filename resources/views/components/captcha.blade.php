@if (empty($captcha_initialized)) 
@push('app.scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
@php
	$captcha_initialized = true;
@endphp
@endif


<div id="recaptcha" class="g-recaptcha float-end" data-sitekey="{{ (Config::get('recaptcha'))['client_secret'] }}"></div>

