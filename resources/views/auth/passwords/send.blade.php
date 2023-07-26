@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Reset Password') }}</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('password.send') }}">
                        {{ csrf_field() }}

						@if (empty($user))
                        <div class="form-group{{ $errors->has('uid') ? ' has-error' : '' }}">
                            <label for="uid" class="col-md-4 control-label">{{ __('Phone or email') }}</label>

                            <div class="col-md-6">
                                <input id="uid" type="text" class="form-control" name="uid" value="{{ old('uid') }}" required>

                                @if ($errors->has('uid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('uid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @else
                        	<input type="hidden" name="uid" value="{{ $user }}" />
                        @endif
                        
                        <div class="form-group row">
							<label for="preferred" class="col-md-4 control-label">{{ __('Preferred verification method') }}</label>

							<div class="col-md-6">
								<input id="preferred_sms" type="radio" name="preferred" value="sms" checked>&nbsp;{{ __('SMS') }}</input>
								<input id="preferred_email" type="radio" name="preferred" value="email">&nbsp;{{ __('E-mail') }}</input>
							</div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Code') }} 
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

				<div class="panel-heading">
					{{ __('Reset password using') }}:
				</div>
				
				<div class="panel-body">
					<div class="form-group row">
						@if (!empty($idp))
						@foreach ($idp as $name) 
							<div class="col-md-4">
								<a class="btn btn-social" href="{{ route('password.eidp', ['client' => $name ]) }}">
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
