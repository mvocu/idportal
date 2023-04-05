@extends('layouts.app')

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	

	<div class="card mt-5">

         @if (session('status') || $errors->has('failure'))
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
		</div>
		@endif


		<div class="card-body pb-2">
			<h5 class="card-title">{{ __('Security key') }}</h5>
			<p>{{ __('mfa.webauthn-description') }}</p> 
		</div>
		
		@if ( !empty($webauthn) && !$webauthn->isEmpty() )
		<div class="card-body py-1">
			<p>{{ __('You have registered the following security keys.') }}</p>
			<ul class="list-group">
				@foreach ($webauthn as $device)
				<li class="list-group-item py-3">
					<div class="d-flex flex-row align-items-center">
						@if (empty($device->getAttestationMetadata()->deviceProperties->imageUrl))
						<div class="col-sm-2">
							<i class="bi bi-usb-drive fs-1"></i>
						</div>
						@else
						<div class="col-sm-3">
							<img class="card-img-left" src="{{ $device->getAttestationMetadata()->deviceProperties->imageUrl }}"></img>
						</div>						
						@endif
						<div class="col-sm-9">
							<strong>{{ $device->getName() }}</strong>
							<p>{{ $device->getAttestationMetadata()->deviceProperties->displayName }}</p>
							<p>{{ __('Registered at') }} {{ $device->getRegistrationDate() }}</p> 
						</div>
					</div>
				</li> 
				@endforeach
				@if (0)
				<li class="list-group-item py-3 text-center">
					<a href="{{ route('mfa.webauthn.adddevice') }}" class="btn btn-default"><i class="fa fa-plus-circle me-2"></i>{{ __('Add new device') }}</a>
				</li>
				@endif
			</ul>

		</div>

		<div class="card-body">
			<div class="d-flex flex-row justify-content-end align-items-start">
					<a class="btn btn-info" role="button" href="{{ route('mfa.webauthn.test') }}"><i class="fa fa-question me-2"></i>{{ __('Test') }}</a>
					<form method="POST" class="" action="{{ route('mfa.webauthn.delete') }}">
						@csrf
						@method('DELETE')
						
						<button class="btn btn-primary" type="submit"><i class="fa fa-times me-2"></i>{{ __('Remove all') }}</button>
					</form>
			</div>
		</div>

		@else
		<div class="card-body py-1">
			<div class="alert alert-info text-align-center">
			<i class="fa fa-info me-2"></i>
			{{ __('You have no registered security keys.') }}
			</div>
			<div class="d-flex flex-row justify-content-center align-items-start my-4">
					<form method="POST" class="" action="{{ route('mfa.webauthn.add') }}">
						@csrf
						<button class="btn btn-primary" type="submit"><i class="fa fa-plus-circle me-2"></i>{{ __('Register') }}</button>
					</form>
			</div>
			<p>{{ __('mfa.reauth-description') }}</p>
		</div>
		@endif
				
		
		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('mfa.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

	</div>
	
</div>

@endsection