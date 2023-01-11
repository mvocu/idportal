@extends('layouts.app')

@section('content')

<div class="d-flex flex-column" style="max-width: 720px">	
	<div class="card mt-5">
		<div class="card-body">
			<h4 class="card-title">{{ __('Multifactor authentication') }}</h4>
			<div class="d-flex flex-row justify-content-between">
				<p>{{ $policy->getDescription() }}</p>
				@if ( $policy->isOn() )
					<a class="btn btn-primary" role="button">{{ __('Turn off') }}</a>
				@else
					<a class="btn btn-danger" role="button">{{ __('Turn on') }}</a>
				@endif
			</div>
		</div> 
	</div>
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
						<p>{{ __('You have registered the following security keys.') }}</p>
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
		<p>You will not be asked to use second factor when authenticating from one of those devices.</p>
	</div>
</div>

@endsection
