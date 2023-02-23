@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Registration') }}</div>

				<script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <div class="panel-body" id="vue-app">

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}

						@component('components.smsauthorization')
						    @slot('url')
						    {{ route('register.authorize') }}
						    @endslot
						@endcomponent
						
                        <div class="form-group row {{ $errors->has('firstname') ? ' has-error' : '' }}">
                            <label for="firstname" class="col-md-4 col-form-label text-md-right control-label">{{ __('First name') }}</label>

                            <div class="col-md-6">
                                <input id="firstname" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" required autofocus>

                                @if ($errors->has('firstname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('firstname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('lastname') ? ' has-error' : '' }}">
                            <label for="lastname" class="col-md-4 col-form-label text-md-right control-label">{{ __('Last name') }}</label>

                            <div class="col-md-6">
                                <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" required autofocus>

                                @if ($errors->has('lastname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('lastname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label"><em>{{ __('E-mail address') }}</em></label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

				<div class="panel-heading">
					{{ __('Register using') }}:
				</div>
				
				<div class="panel-body">
					<div class="form-group row">
						@if (!empty($idp))
						@foreach ($idp as $name) 
							<div class="col-md-4">
								<a class="btn btn-social" href="{{ route('register.eidp', ['client' => $name ]) }}">
								   <span class="fa fa-openid"></span> 
								   {{ $name }}
								</a>
							</div>
						@endforeach
						@endif
					</div>
				</div>
				                
            </div>
        </div>
    </div>
</div>
@endsection
