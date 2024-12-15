@extends('layouts.app')

@section('header')
<li class="">
	<a href="{{ route('reset.methods') }}" class="btn btn-floating me-4"><i class="fas fa-arrow-left"></i></a>
</li>
<li>
	<span>{{ __('Getting new password for CAS') }}</span>
</li>
@endsection

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	

    @if (session('status') || session('warning') || $errors->has('failure'))
	         @if (session('status'))
	      	<div class="alert alert-success mt-4" role="alert">
    	        {{ session('status') }}
             </div>
             @endif

	         @if (session('warning'))
	      	<div class="alert alert-warning mt-4" role="alert">
    	        {{ session('warning') }}
             </div>
             @endif

             @if ($errors->has('failure'))
             <div class="alert alert-danger mt-4" role="alert">
             	{{ $errors->first('failure') }}
             </div>
             @endif
	@endif

	<div class="border-bottom border-2 border-primary mt-5">
			<h5 class="">{{ __('Locate your account') }}</h5>
			<p>{{ __('Fill information necessary to identify your account:') }}</p>
	</div>


	<div class="d-flex flex-sm-row flex-column flex-wrap align-items-stretch justify-content-between mt-5">
		<div class="col-sm-5 mb-2">
		    <form class="h-100" method="POST" action="{{ route('reset.search') }}" aria-label="{{ __('Search form') }}">
			@csrf
			<div class="card d-flex flex-column align-items-stretch h-100">
			<div class="card-header text-center flex-grow-0">
				{{ __('Search by identifier') }}
			</div>					
			<div class="card-body">
				<div class="form-outline mb-3">
    	        	<input id="cunipersonalid" type="text" class="form-control{{ $errors->has('cunipersonalid') ? ' is-invalid' : '' }}" name="cunipersonalid" value="{{ old('cunipersonalid') }}" required />
                	@if ($errors->has('cunipersonalid'))
                	<span class="invalid-feedback" role="alert">
                    	<strong>{{ $errors->first('cunipersonalid') }}</strong>
                	</span>
	            	@endif
            		<label for="identifier" class="form-label">{{ __('Identifier') }}</label>
                </div>
				<p>{{ __('Use your personal number, login name, e-mail address or phone number as an identifier.') }}</p>
			</div>
			<div class="card-body text-center flex-grow-0">
				<button type="submit" class="btn btn-primary">{{ __('Proceed') }}</button>
			</div>
			</div>
			</form>
		</div>
		<div class="col-sm-2 mb-2 text-center">
			<span class="text-strong">{{ __('OR') }}</span>
		</div>
		<div class="col-sm-5 mb-2">
		    <form class="h-100" method="POST" action="{{ route('reset.search') }}" aria-label="{{ __('Search form') }}">
			@csrf
			<div class="card d-flex flex-column align-items-stretch h-100">
			<div class="card-header text-center flex-grow-0">
				{{ __('Search by personal data') }}
			</div>					
			<div class="card-body">
				<div class="form-outline mb-3">
		        	<input id="given_name" type="text" class="form-control{{ $errors->has('given_name') ? ' is-invalid' : '' }}" name="given_name" value="{{ old('given_name') }}" required />
                	@if ($errors->has('given_name'))
                	<span class="invalid-feedback" role="alert">
                    	<strong>{{ $errors->first('given_name') }}</strong>
                	</span>
                	@endif
            		<label for="given_name" class="form-label">{{ __('First name') }}</label>
                </div>
				<div class="form-outline mb-3">
    	        	<input id="family_name" type="text" class="form-control{{ $errors->has('family_name') ? ' is-invalid' : '' }}" name="family_name" value="{{ old('family_name') }}" required />
                	@if ($errors->has('family_name'))
                	<span class="invalid-feedback" role="alert">
                    	<strong>{{ $errors->first('family_name') }}</strong>
                	</span>
                	@endif
            		<label for="family_name" class="form-label">{{ __('Last name') }}</label>
                </div>
				<div class="form-outline mb-4">
    	        	<input id="birthdate" type="date" class="form-control{{ $errors->has('birthdate') ? ' is-invalid' : '' }}" name="birthdate" value="{{ old('birthdate') }}" required />
                	@if ($errors->has('birthdate'))
                	<span class="invalid-feedback" role="alert">
                    	<strong>{{ $errors->first('birthdate') }}</strong>
                	</span>
                	@endif
            		<label for="birthdate" class="form-label">{{ __('Date of birth') }}</label>
                </div>
			</div>
			<div class="card-body text-center flex-grow-0">
				<button type="submit" class="btn btn-primary">{{ __('Proceed') }}</button>
			</div>
			</div>
			</form>
		</div>
	</div>
	</form>

</div>

@endsection