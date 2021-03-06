@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Account Activation') }}</div>

                <div class="card-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('activate.activate') }}" aria-label="{{ __('Activate Account') }}">
                        @csrf

						@if (empty($token)) 

                        <div class="form-group row">
                            <label for="token" class="col-md-4 col-form-label text-md-right">{{ __('Authorization token') }}</label>

                            <div class="col-md-6">
                                <input id="token" type="text" class="form-control{{ $errors->has('token') ? ' is-invalid' : '' }}" name="token" value="{{ $token ?? old('token') }}" required autofocus>

                                @if ($errors->has('token'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('token') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						@else
						
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        @endif

						@if (empty($uid) && empty(old('uid'))) 
						
                        <div class="form-group row">
                            <label for="uid" class="col-md-4 col-form-label text-md-right">{{ __('E-mail address') }}</label>

                            <div class="col-md-6">
                                <input id="uid" type="text" class="form-control{{ $errors->has('uid') ? ' is-invalid' : '' }}" name="uid" value="{{ $uid ?? old('uid') }}" required autofocus>

                                @if ($errors->has('uid'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('uid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        @else 
                        
                        <input type="hidden" name="uid" value="{{ $uid ?? old('uid') }}">
                        
                        @endif

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Activate Account') }}
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
