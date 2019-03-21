@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Reset Password') }}</div>

                <div class="panel-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('password.update') }}">
                        {{ csrf_field() }}



						@if (empty($token)) 

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="token" class="col-md-4 control-label">{{ __('Authorization Token') }}</label>

                            <div class="col-md-6">
                                <input id="token" type="text" class="form-control" name="token" value="{{ $token ?? old('token') }}" required autofocus>

                                @if ($errors->has('token'))
                                    <span class="help-block" role="alert">
                                        <strong>{{ $errors->first('token') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						@else
						
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        @endif

						@if (empty($uid) && empty(old('uid'))) 

                        <div class="form-group{{ $errors->has('uid') ? ' has-error' : '' }}">
                            <label for="uid" class="col-md-4 control-label">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="uid" type="text" class="form-control" name="uid" value="{{ $uid or old('uid') }}" required autofocus>

                                @if ($errors->has('uid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('uid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        @else 
                        
                        <input type="hidden" name="uid" value="{{ $uid ?? old('uid') }}">
                        
                        @endif

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="col-md-4 control-label">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
