@extends('layouts.app')

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	
	<div class="card mt-5">
		<div class="card-body">
			<h4 class="card-title">{{ __('Unregistered external identity') }}</h4>
			<p class="alert alert-info">
				<i class="fa fa-info me-2"></i>
				{{ __('You have signed in using account that is not yet registered with CAS. You have to register this account with CAS to continue.') }}
			</p>

			<h5>{{ __('Information about external account') }}</h5>			
			<div class="container border rounded rounded-4 p-4">
				<div class="d-flex flex-row row">
					<div class="col-sm-4">{{ __('Identity provider') }}</div>
					<div class="col-sm-8 fw-bold">{{ __($provider) }}</div>
				</div>
				<div class="d-flex flex-row row mb-2">
					<div class="col-sm-4">{{ __('Identifier') }}</div>
					<div class="col-sm-8 fw-bold">{{ $auth_user->getAuthIdentifier() }}</div>
				</div>
				@foreach ($names as $name)
					@if (null !== data_get($attrs, $name))
					<div class="d-flex flex-row row">
						<div class="col-sm-4">{{ __($name) }}</div>
						<div class="col-sm-8 fw-bold">{{ data_get($attrs, $name) }}</div>
					</div>
					@endif
				@endforeach
			</div>

			<p class="mt-2 fw-bold">{{ __('Data displayed above is only for your information, it will not be stored and further processed except the anonymous identifier.') }}</p>
			<p class="mt-4">{{ __('To register this account, you have to sign-in using your CAS account to continue.') }}</p>
			<div class="d-flex flex-row justify-content-end align-items-start">
				<a class="btn btn-primary" role="button" href="{{ route('ext.login') }}"><i class="fa fa-lock me-2"></i>{{ __('Login with CAS') }}</a>
			</div>
		</div> 
		
	</div>

</div>

@endsection
