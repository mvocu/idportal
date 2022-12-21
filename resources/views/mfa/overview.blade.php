@extends('layouts.app')

@section('content')

<div class="d-flex flex-column" style="max-width: 720px">	
	<div class="card mt-5">
		<div class="card-body">
			<h4 class="card-title">{{ __('Multifactor authentication') }}</h4>
		</div> 
	</div>
	<div class="border-bottom border-2 border-primary">
		<h5 class="mt-5">{{ __('Available methods') }}</h5>
		<p>These methods are available as second factor.</p>
	</div>
	<div class="card mt-4">
		<ul class="list-group">
			<li class="list-group-item d-flex flex-row justify-content-between p-3">
				<div class="text-center" style="width: 64px"><i class="fa fa-mobile-alt fa-3x"></i></div>
				<div class="flex-grow-1 px-4">
					<h5>{{ __('Mobile phone') }}</h5>
				</div>
				<a class="stretch-link btn btn-light btn-lg" role="button"><i class="fa fa-angle-right"></i> </a>
			</li>
			<li class="list-group-item  d-flex flex-row justify-content-between p-3">
				<div class="text-center" style="width: 64px"><i class="fa fa-key fa-2x"></i></div>
				<div class="flex-grow-1 px-4">
					<h5>{{ __('Security key') }}</h5>
				</div>
				<a class="stretch-link btn btn-light btn-lg" role="button"><i class="fa fa-angle-right"></i> </a>
			</li>
			<li class="list-group-item  d-flex flex-row justify-content-between p-3">
				<div class="text-center"  style="width: 64px"><i class="fa fa-envelope fa-2x"></i></div>
				<div class="flex-grow-1 px-4">
					<h5>{{ __('SMS message') }}</h5>
				</div>
				<a class="stretch-link btn btn-light btn-lg" role="button"><i class="fa fa-angle-right"></i> </a>
			</li>
		</ul>
	</div>
	<div class="border-bottom border-2 border-primary">
		<h5 class="mt-5">{{ __('Registered trusted devices') }}</h5>
		<p>You will not be asked to use second factor when authenticating from one of those devices.</p>
	</div>
</div>

@endsection
