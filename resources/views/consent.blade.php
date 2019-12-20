@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Terms of usage') }}</div>

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


                    <form method="POST" action="{{ route('consent.set') }}" aria-label="{{ __('Set consent') }}">
                        @csrf

						<div class="form-group row">
							<div class="col-12">
								{{ __('Before using this website you are required to agree the terms of usage, which are available') }} 
								<a href="/documents/terms.pdf">{{ __('here') }}</a>.
							</div>
						</div>
                        
						<div class="form-group row">
							<div class="col-12">
								{{ __('If you do not agree to the terms, your account will be disabled.') }} 
							</div>
						</div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
                                <input id="consent_check" type="checkbox" class="" name="consent_check" value="agree" required>
	                            <label for="consent_check" class="col-form-label">{{ __('I have read the terms above and agree') }}</label>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }} 
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
