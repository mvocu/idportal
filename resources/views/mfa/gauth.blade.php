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
			<h5 class="card-title">{{ __('Mobile phone application') }}</h5>
			<p>{{ __('mfa.gauth-description') }}</p> 
		</div>
		
		@if ( !empty($gauth) && !$gauth->isEmpty() )
		<div class="card-body py-1">
			<p>{{ __('List of devices you can use to obtain confirmation code.') }}</p>
			<ul class="list-group">
				@foreach ($gauth as $device)
				<li class="list-group-item">
					<div class="d-flex flex-row align-items-center">
						<div class="col-sm-1"><i class="bi bi-phone fs-2"></i></div>
						<div class="col-sm-4 fw-bold">{{ $device->getName() }}</div>
					</div>
					<div class="container offset-sm-1">
						<p>{{ __('Registered at') }} {{ $device->getRegistrationDate() }}</p>
						<h6>{{ __('Emergency scratch codes') }}:</h6>
						<strong>{{ join(", ", $device->getScratchCodes()) }}</strong>
					</div>
				</li> 
				@endforeach
			</ul>
		</div>
		
		<div class="card-body">
			<div class="d-flex flex-row justify-content-end align-items-start">
					<a class="btn btn-info" role="button" href="{{ route('mfa.gauth.test') }}"><i class="fa fa-question me-2"></i>{{ __('Test') }}</a>
					<form method="POST" class="" action="{{ route('mfa.gauth.delete') }}">
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
			{{ __('You have no registered devices to generate confirmation codes.') }}
			</div>
			<div class="d-flex flex-row justify-content-center align-items-start my-4">
					<form method="POST" class="" action="{{ route('mfa.gauth.add') }}">
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