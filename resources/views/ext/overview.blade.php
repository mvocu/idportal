@extends('layouts.app')

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	

@guest
	<div class="card mt-5">
		<div class="card-body">
			<p>{{ __('You are not signed in. Please login to continue.') }}</p>
			<div class="d-flex flex-row justify-content-end align-items-start">
				<a class="btn btn-primary" role="button" href="{{ route('login') }}"><i class="fa fa-lock me-2"></i>{{ __('Login') }}</a>
			</div>
		</div> 
	</div>
@endguest

@auth
	<div class="border-bottom border-2 border-primary">
	</div>

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

		<div class="card-body">
			<h5 class="card-title">{{ __('External identities') }}</h5>
			<p>{{ __('These external identy providers are available to login with CAS:') }}</p>

			<ul class="list-group mt-2">
				@foreach ($providers as $provider)
				<li class="list-group-item p-3">
					<div class="d-flex flex-row justify-content-between">
						<div class="text-center" style="width: 64px"><i class="fa fa-user-lock fa-3x"></i></div>
						<div class="flex-grow-1 ps-2">
							@if ( !array_key_exists(strtolower($provider), $ext_ids) )
							<a class="stretch-link btn btn-light btn-lg float-end ms-2" href="{{ route('ext.login.ext', [ 'provider' => $provider ]) }}" role="button">
								<i class="fa fa-plus me-2"></i>{{ __('Add external ID') }}</a>
							@else
							<form method="POST"  action="{{ route('ext.remove', [ 'provider' => $provider ]) }}">
								@csrf
								<button type="submit" class="stretch-link btn btn-primary btn-lg float-end">
									<i class="fa fa-minus me-2"></i>
									{{ __('Remove external ID') }}
								</button>
							</form>
							@endif
							<h5>{{ __($provider) }}</h5>
							<p>{{ __($provider . "-desc") }}</p>
						</div>
					</div>					
					<div class="container">
						@if ( array_key_exists(strtolower($provider), $ext_ids) )
						<div class="row align-items-center">
							<div class="col-sm-4">{{ __('Registered ID:') }}</div>
							<div class="col-sm-8 fw-bold">{{ $ext_ids[strtolower($provider)] }}</div>
						</div>
						@else
						<div class="row">
							<p class="fw-bold">{{ __('You have no registered external identity.') }}</p>
						</div> 
						@endif
					</div>
				</li>
				@endforeach

		</div> 
	</div>
@endauth

</div>

@endsection