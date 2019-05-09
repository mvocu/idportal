@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.send') }}" aria-label="{{ __('Reset Password') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="uid" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="uid" type="text" class="form-control{{ $errors->has('uid') ? ' is-invalid' : '' }}" name="uid" value="{{ old('uid') }}" required>

                                @if ($errors->has('uid'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('uid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group row">
							<label for="preferred" class="col-md-4 col-form-label text-md-right">{{ __('Preferred verification method') }}</label>

							<div class="col-md-6">
								<input id="preferred_sms" type="radio" name="preferred" value="sms" checked>&nbsp;{{ __('SMS') }}</input>
								<input id="preferred_email" type="radio" name="preferred" value="email">&nbsp;{{ __('E-mail') }}</input>
							</div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Code') }} 
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
