@extends('layouts.app')

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	
	<div class="card mt-5">
		<div class="card-body">
			<h4 class="card-title">{{ __('Multifactor authentication') }}</h4>
			<p class="mb-2">{{ __($policy->getDescription()) }}</p>
			@if ( $policy->isOn() )
			<div class="d-flex flex-row flex-wrap justify-content-end align-items-stretch" style="gap: 0.5rem">
					<a class="btn btn-info col-12 col-sm-4 m-0" role="button" href="{{ route('mfa.policy') }}"><i class="fa fa-cog me-2"></i>{{ __('Choose policy') }}</a>
					<form method="POST" class="col-12 col-sm-4" action="{{ route('mfa.policy.set') }}">
						@csrf
						
						<input type="hidden" name="policy" value="none" />
						
						<button class="btn btn-primary w-100 h-100 m-0" type="submit"><i class="fa fa-power-off me-2"></i>{{ __('Turn off') }}</button>
					</form>
			</div>
			@else
			<div class="d-flex flex-row justify-content-end align-items-start">
				<a class="btn btn-danger" role="button" href="{{ route('mfa.policy') }}"><i class="fa fa-power-off me-2"></i>{{ __('Turn on') }}</a>
			</div>
			@endif
		</div> 
	</div>
	
	@if ( $policy->isOn() )
	<div class="border-bottom border-2 border-primary">
		<h5 class="mt-5">{{ __('Available methods') }}</h5>
		<p>{{ __('These methods are available as a second factor.') }}</p>
	</div>
	<div class="card mt-4">
		<ul class="list-group">
			<li class="list-group-item p-3">
				<div class="d-flex flex-row justify-content-between">
					<div class="text-center" style="width: 64px"><i class="fa fa-mobile-alt fa-3x"></i></div>
					<div class="flex-grow-1 ps-2">
						<a class="stretch-link btn btn-light btn-lg float-end" href="{{ route('mfa.gauth') }}" role="button"><i class="fa fa-angle-right"></i> </a>
						<h5>{{ __('Mobile phone application') }}</h5>
						<p>{{ __('List of devices you can use to obtain confirmation code.') }}</p>
					</div>
				</div>					
				<div class="container offset-sm-1">
					@if ( !empty($gauth) )
						@foreach ($gauth as $device)
						<div class="row align-items-center">
							<div class="col-sm-1"><i class="bi bi-phone fs-2"></i></div>
							<div class="col-sm-4 fw-bold">{{ $device->getName() }}</div>
							<div class="col-sm-7">{{ __('Registered at') }} {{ $device->getRegistrationDate() }} </div>
						</div> 
						@endforeach
					@endif
				</div>
			</li>
			<li class="list-group-item p-3">
				<div class="d-flex flex-row justify-content-between">
					<div class="text-center pb-2" style="width: 64px"><i class="fa fa-key fa-2x"></i></div>
					<div class="flex-grow-1 ps-2">
						<a class="stretch-link btn btn-light btn-lg float-end" href="{{ route('mfa.webauthn') }}" role="button"><i class="fa fa-angle-right"></i> </a>
						<h5>{{ __('Security key') }}</h5>
						@if ( !empty($webauthn) && !$webauthn->isEmpty())
						<p>{{ __('You have registered the following security keys.') }}</p>
						@else
						<p>{{ __('You have no registered security keys.') }}</p>
						@endif
					</div>
				</div>
				<div class="container offset-sm-1"">
					@if ( !empty($webauthn) )
						@foreach ($webauthn as $device)
						<div class="row align-items-center">
							<div class="col-sm-1"><i class="bi bi-usb-drive fs-2"></i></div>
							<div class="col-sm-4 fw-bold">{{ $device->getName() }}</div>
							<div class="col-sm-7">{{ __('Registered at') }} {{ $device->getRegistrationDate() }} </div>
						</div> 
						@endforeach
					@endif
				</div>
			</li>
			<li class="list-group-item d-flex flex-row justify-content-between p-3">
				<div class="text-center pb-2"  style="width: 64px"><i class="fa fa-envelope fa-2x"></i></div>
				<div class="flex-grow-1 ps-2">
					<a class="stretch-link btn btn-light btn-lg float-end" href="{{ route('mfa.sms') }}" role="button"><i class="fa fa-angle-right"></i> </a>
					<h5>{{ __('SMS message') }}</h5>
					@if (!empty($sms))
					<h6 class="fw-bold">{{ $sms }}</h6>
					<p>{{ __('Verification codes will be sent by SMS to this number.') }}</p>
					@else 
					@endif
				</div>
			</li>
		</ul>
	</div>
	<div class="border-bottom border-2 border-primary">
		<h5 class="mt-5">{{ __('Registered trusted devices') }}</h5>
		<p>{{ __('You will not be asked to use second factor when authenticating from one of those devices.') }}</p>
	</div>
	<div class="card mt-4 mb-5">

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

		@if (!empty($trusted) && !$trusted->isEmpty())
		<ul class="list-group list-group-light">
				@foreach ($trusted as $device) 
				<li class="list-group-item p-3">
					<div class="row align-items-center">
						<div class="col-sm-4 fw-bold">{{ $device->getName() }}</div>
						<div class="col-sm-7">{{ __('Expires at') }} {{ $device->getExpirationDate() }} </div>
					</div> 
				</li>
				@endforeach
		</ul>
		<div class="card-footer">
			<div class="d-flex flex-row justify-content-end align-items-start">
				<a class="btn btn-default" role="button" href="{{ route('mfa.trusted') }}"><i class="fa fa-cog me-2"></i>{{ __('Review list') }}</a>
				<form method="POST" class="" action="{{ route('mfa.trusted.delete') }}">
					@csrf
					@method('DELETE')
						
					<input type="hidden" name="device" value="all" />
						
					<button class="btn btn-danger" role="button"><i class="fa fa-times me-2"></i>{{ __('Clear all') }}</button>
				</form>
			</div>
		</div>
		@else
		<div class="card-body">
			<div class="alert alert-info text-align-center">
			<i class="fa fa-info me-2"></i>
			{{ __('You have no registered trusted devices.') }}
			</div>
		</div>
		@endif
		
	</div>
	@endif
	
</div>

@endsection
