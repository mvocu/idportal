<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<sms-authorization 
  v-bind:phone="{ label: '{{ __('Phone number') }}', old: '{{ old('phone') }}', valid: {{ $errors->has('phone') ? 0 : 1 }} }"
  v-bind:token="{ label: '{{ __('Authorization token') }}', old : '{{ old('token') }}', valid: {{ $errors->has('token') ? 0 : 1 }} }"
  v-bind:send="{ label: '{{ __('Send authorization code') }}', url: '{{ $url }}' }"
  v-bind:recaptcha="{ client_secret: '{{ (Config::get('recaptcha'))['client_secret'] }}' }"
  >
<template name="phone-error">
                                @if ($errors->has('phone'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
</template>
<template name="token-error">
                                @if ($errors->has('token'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('token') }}</strong>
                                    </span>
                                @endif
</template>
</sms-authorization>

                        