@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Voting code for the participative budget') }}</div>

				<!-- 
				<script src="https://www.google.com/recaptcha/api.js" async defer></script>
                -->				
                <div class="panel-body" id="vue-app">

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('voting.register') }}">
                        {{ csrf_field() }}

						 @if (0)
                         <div class="form-group row">
                            <div class="col-md-6 col-md-offset-4">
								<div class="g-recaptcha" data-sitekey="{{ (Config::get('recaptcha'))['client_secret'] }}"></div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row {{ $errors->has('firstname') ? ' has-error' : '' }}">
                            <label for="firstname" class="col-md-4 col-form-label text-md-right control-label">{{ __('First name') }}</label>

                            <div class="col-md-6">
								@if (empty($user_r['first_name']))
                                <input id="firstname" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" required autofocus />

                                @if ($errors->has('firstname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('firstname') }}</strong>
                                    </span>
                                @endif
                                @else 
                                <span class="form-control">{{ $user_r['first_name'] }}</span>
                                <input id="firstname" type="hidden" name="firstname" value="{{ $user_r['first_name'] }}" />
                                @endif
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('lastname') ? ' has-error' : '' }}">
                            <label for="lastname" class="col-md-4 col-form-label text-md-right control-label">{{ __('Last name') }}</label>

                            <div class="col-md-6">
								@if (empty($user_r['last_name']))
                                <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" required autofocus />

                                @if ($errors->has('lastname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('lastname') }}</strong>
                                    </span>
                                @endif
                                @else
                                <span class="form-control">{{ $user_r['last_name'] }}</span>
                                <input id="lastname" type="hidden" name="lastname" value="{{ $user_r['last_name'] }}" />
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('birth_year') ? ' has-error' : '' }}">
                            <label for="birth_year" class="col-md-4 col-form-label text-md-right control-label">{{ __('Year of birth') }}</label>

                            <div class="col-md-6">
								@if (empty($user_r['birth_date']))
                                <input id="birth_year" type="text" class="form-control" name="birth_year" value="{{ old('birth_year') }}" required />

                                @if ($errors->has('birth_year'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('birth_year') }}</strong>
                                    </span>
                                @endif
                                @else
                                <span class="form-control">{{ date('Y', strtotime($user_r['birth_date'])) }}</span>
                                <input id="birth_year" type="hidden" name="birth_year" value="{{ date('Y', strtotime($user_r['birth_date'])) }}" />
                                @endif
                            </div>
                        </div>

						@if (0)
						@component('components.smsauthorization')
						    @slot('url')
						    {{ route('register.authorize') }}
						    @endslot
						@endcomponent
						@endif

                         <div class="form-group row{{ $errors->has('phone') ? ' has-error': '' }}">
                            <label for="phone" class="col-md-4 col-form-label text-md-right control-label"><em>{{ __('Phone number') }}</em></label>

                            <div class="col-md-6">
                            @php
                               $phone = old('phone');
                               if(empty($phone) && !empty($user_r['phones'])) {
                                   $phone = $user_r['phones'][0]['phone'];
                               }
                            @endphp
                                <input id="phone" type="text" class="form-control" name="phone" value="{{ $phone }}">

                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
						
						
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label"><em>{{ __('E-mail address') }}</em></label>

                            <div class="col-md-6">
                            @php
                               $email = old('email');
                               if(empty($email) && !empty($user_r['emails'])) {
                                   $email = $user_r['emails'][0]['email'];
                               }
                            @endphp
                                <input id="email" type="email" class="form-control" name="email" value="{{ $email }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
							<label for="preferred" class="col-md-4 control-label">{{ __('Preferred verification method') }}</label>

							<div class="col-md-6" style="padding-top: 6px">
								<input id="preferred_sms" type="radio" name="preferred" value="sms" checked>&nbsp;{{ __('SMS') }}</input>
								<input id="preferred_email" type="radio" name="preferred" value="email">&nbsp;{{ __('E-mail') }}</input>
							</div>
                        </div>

						<div class="form-group row">
						</div>

						<div class="form-group row">
							<div class="col-md-10 col-md-offset-1"><em>
								{{ __('Before using this website you are required to agree the terms of usage, which are available') }} 
								<a href="/documents/terms.pdf">{{ __('here') }}</a>.</em>
							</div>
                            <div class="col-md-8 col-md-offset-3 row" style="padding-top: 8px">
                                <input id="gdpr_check" type="checkbox" class="col-xs-1" style="padding-top: 8px" name="gdpr_check" value="agree" required>
	                            <label for="gdpr_check" class="col-xs-11">{{ __('I have read the terms above and agree') }}</label>
							</div>
						</div>
                        
                        <div class="form-group row">
							<div class="col-md-10 col-md-offset-1"><em>
								{{ __('I declare that I have read the participative budget voting regulation') }} <a href="https://mojeobec.kr-stredocesky.cz/portal/paroz/uvaly/zasady">{{ __('here') }}</a>
								{{ __('and I will adhere to the stated conditions.') }}</em> 
							</div>
	                        
                            <div class="col-md-8 col-md-offset-3 row" style="padding-top: 8px">
	                            <input id="consent_check" type="checkbox" class="col-xs-1" name="consent_check" value="agree" required>
								<label for="consent_check" class="col-xs-11">{{ __('I confirm the above declaration') }}</label>	                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Obtain voting code') }}
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>

				@if (empty($user_r))
				<div class="panel-heading">
					{{ __('Ask for voting code using') }}:
				</div>
				
				<div class="panel-body">
					<div class="form-group row">
						@if (!empty($idp))
						@foreach ($idp as $name) 
							<div class="col-md-4">
								<a class="btn btn-social" href="{{ route('voting.register.eidp', ['client' => $name ]) }}">
								   <span class="fa fa-openid"></span> 
								   {{ __($name) }}
								</a>
							</div>
						@endforeach
						@endif
					</div>
				</div>
				@else
            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-2"><a href="{{ route('eidp.logout', [ 'client' => $client ]) }}" class="btn btn-default" aria-label="{{ __('Use another identity') }}">{{ __('Use another identity') }}</a></div>
					</div>
            	</div>
				@endif
								                
            </div>
        </div>
    </div>
</div>
@endsection
