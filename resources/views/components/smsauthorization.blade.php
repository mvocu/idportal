<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<sms-authorization 
  v-bind:phone="{ label: '{{ __('Phone number') }}', old: '{{ old('phone') }}', valid: {{ $errors->has('phone') ? false : true }} }"
  v-bind:token="{ label: '{{ __('Authorization token') }}', old : '{{ old('token') }}', valid: {{ $errors->has('token') ? false : true }} }"
  v-bind:send="{ label: '{{ __('Send authorization code') }}', url: '{{ $url }}' }"
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

                        