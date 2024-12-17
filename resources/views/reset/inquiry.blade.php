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

	@if (false)
	<div class="border-bottom border-2 border-primary mt-5">
			<h5 class="">{{ __('Additional information') }}</h5>
			<p>{{ __('More information is required to verify your identity:') }}</p>
	</div>
	@endif

	<div class="card d-flex flex-column align-items-stretch mt-5">

    @if (session('status') || session('warning') || $errors->has('failure'))
		<div class="card-body">
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
		</div>             
	@endif
	
		<h4 class="card-title mt-4 text-center">{{ __('Review personal data') }}</h4>
		<div class="card-body">
			<em>{{ __('Some important data about you are still missing, and we are not able to establish your identity. Please provide us with more data by filling in some of the requested information below') }}</em>
		</div>

		<div class="card-body">
			<h5>{{ __('Currently known data') }}</h5>
			@if (empty($identity))
			<div class="alert alert-info">
				{{ __('There are no established identity data available.') }}
			</div>
			@else			
			<x-identity :identity="$identity" />

            <div class="text-center mt-4">
				<a href="{{ route('reset.restart') }}" class="btn btn-secondary">{{ __('Reset data') }}</a> 
			</div>
			
			@endif
		</div>

	    <form class="h-100" method="POST" action="{{ route('reset.merge') }}" aria-label="{{ __('Additional personal data form') }}">
		<div class="card-body">
			<h5>{{ __('Additional information') }}</h5>
			@csrf

			
			<div class="mb-3">
				<label for="given_name" class="form-label form-text">{{ __('Personal data') }}</label>
				<div class="row g-3">
				<div class="col-sm-12">
					@if (empty($identity['given_name']))
					<x-input type="text" name="given_name" :value="old('given_name')" :errors="$errors" text="First name" required="true" />
            		@endif
            	</div>
				<div class="col-sm-12">
		            @if (empty($identity['family_name']))
					<x-input type="text" name="family_name" :value="old('family_name')" :errors="$errors" text="Last name" required="true" />
		            @endif
				</div>
				<div class="col-sm-6">
		            @if (empty($identity['birthdate']))
					<x-input type="date" name="birthdate" :value="old('birthdate')" :errors="$errors" text="Date of birth" required="true" />
            		@endif
	            </div>
				<div class="col-sm-6">
				@guest
					<x-captcha />
				@endguest
				</div>
				</div>	
		</div>
@if (0)
		
			<div class="row g-3">
            @if (empty($identity['gender']))
	            <div class="col">
					<div class="form-outline mb-3">
						<x-select id="gender" name="gender" text="Select gender" :value="old('gender')" options="[
							{ value: 'M', label: 'Male' },
							{ value: 'F', label: 'Female' },
							]" />
		            </div>
            	</div>
            @endif
            @if (empty($identity['nationality']))
	            <div class="col">
					<div class="form-outline mb-3">
						<x-select id="nationality" name="nationality" text="Select nationality" :value="old('nationality')" options="[
							{ value: 'cs', label: 'Czech Republic' },
							{ value: 'gb', label: 'United Kingdom' },
							]" />
            		</div>
	            </div>
            @endif
            </div>
@endif
            
			<div class="mb-3">
				<label for="phone_number" class="form-label form-text">{{ __('Add contacts') }}</label>
				<div class="row g-3">
				<div class="col-5">
					<x-input type="tel" name="phone_number" :value="old('phone_number')" :errors="$errors" text="Phone number" />
				</div>					
				<div class="col-7">
					<x-challenge id="phone_challenge" for="phone_number" name="phone_challenge" text="Verify phone number" 
						url="{{ route('challenge.phone') }}" />
				</div>
				<div class="col-5">
					<x-input type="text" name="email" :value="old('email')" :errors="$errors" text="E-mail" />
				</div>
				<div class="col-7">
					<x-challenge id="email_challenge" for="email" name="email_challenge" text="Verify email address" 
						url="{{ route('challenge.email') }}" />
				</div>
            	</div>
			</div>            
			            
			@if (empty($identity['address']))
			@php $identity['address'] = []; @endphp
			<div class="mb-3">
				<label for="street" class="form-label form-text">{{ __('Address of permanent residency') }}</label>
				<div class="row g-3">
				<div class="col-sm-6">
					<x-input type="text" name="street" :value="old('street')" :errors="$errors" text="Street" />
				</div>
				<div class="col-sm-3">
					<x-input type="text" name="street_number" :value="old('street_number')" :errors="$errors" text="Street number" />
				</div>
				<div class="col-sm-3">
					<x-input type="text" name="evidence_number" :value="old('evidence_number')" :errors="$errors" text="Ev. number" />
				</div>
				<div class="col-sm-8">
					<x-input type="text" name="city" :value="old('city')" :errors="$errors" text="City" />
				</div>
				<div class="col-sm-4">
					<x-input type="text" name="postal_code" :value="old('postal_code')" :errors="$errors" text="Postal code" />
				</div>
				<div class="col-sm-12">
					<div class="form-outline">
						<x-select id="country" name="country" text="Select country" :value="old('address.country')" options="[
							{ value: 'cs', label: 'Czech Republic' },
							{ value: 'gb', label: 'United Kingdom' },
							]" />
            		</div>
				</div>
				</div>
			</div>            
			@endif            
			
            <div class="text-center mt-4">
				<button type="submit" class="btn btn-primary mx-auto">{{ __('Proceed') }}</button>
			</div>
		</div>
		</form>
		
	</div>

</div>

@endsection