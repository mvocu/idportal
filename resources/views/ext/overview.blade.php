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
    @if (session('status') || $errors->has('failure'))
	         @if (session('status'))
	      	<div class="alert alert-success mt-4" role="alert">
    	        {{ session('status') }}
             </div>
             @endif

             @if ($errors->has('failure'))
             <div class="alert alert-danger mt-4" role="alert">
             	{{ $errors->first('failure') }}
             </div>
             @endif
	@endif

	@php
	  $avail = false;
	@endphp
	
	@if (!empty($ext_ids))
	<div class="border-bottom border-2 border-primary mt-5">
			<h5 class="">{{ __('Registered external identities') }}</h5>
			<p>{{ __('You have registered these external identities for login using CAS:') }}</p>
	</div>


	<div class="card mt-4">

<!-- 
		<div class="card-body">
			<h5 class="card-title">{{ __('External identities') }}</h5>
			<p>{{ __('These external identy providers are available to login with CAS:') }}</p>
 -->
			<ul class = "list-group" aclass="list-group mt-2">
				@foreach ($providers as $provider)
				@if (array_key_exists(strtolower($provider), $ext_ids))
					@if (is_array($ext_ids[strtolower($provider)]))
					@php
						$avail = true;
					@endphp
					@foreach ($ext_ids[strtolower($provider)] as $idp => $id)
					<li class="list-group-item p-3">
						<div class="d-flex flex-row justify-content-between">
							<div class="text-center" style="width: 64px"><i class="fa fa-user-lock fa-3x"></i></div>
							<div class="flex-grow-1 ps-2">
							<form method="POST"  action="{{ route('ext.remove', [ 'client' => $provider ]) }}">
								@csrf
								<input type="hidden" name="provider" value="{{ $idp }}" />
								<button type="submit" class="stretch-link btn btn-primary btn-lg float-end">
									<i class="fa fa-minus me-2"></i>
									{{ __('Remove external ID') }}
								</button>
							</form>
							<h5>{{ __($idp) }}</h5>
							<p>{{ __($provider . "-desc") }}</p>
						</div>
					</div>					
					<div class="container">
						<div class="row align-items-center">
							@if (false)
							<div class="col-sm-4">{{ __('Identity provider:') }}</div>
							@endif
							<div class="col-sm-12 fw-bold">{{ __($provider) }}</div>
						</div>
						<div class="row align-items-center">
							<div class="col-sm-4">{{ __('Registered ID:') }}</div>
							<div class="col-sm-8 fw-bold">{{ $id }}</div>
						</div>
					</div>
					</li>
					@endforeach
					@else
					<li class="list-group-item p-3">
						<div class="d-flex flex-row justify-content-between">
							<div class="text-center" style="width: 64px"><i class="fa fa-user-lock fa-3x"></i></div>
							<div class="flex-grow-1 ps-2">
							<form method="POST"  action="{{ route('ext.remove', [ 'client' => $provider ]) }}">
								@csrf
								<button type="submit" class="stretch-link btn btn-primary btn-lg float-end">
									<i class="fa fa-minus me-2"></i>
									{{ __('Remove external ID') }}
								</button>
							</form>
							<h5>{{ __($provider) }}</h5>
							<p>{{ __($provider . "-desc") }}</p>
						</div>
					</div>					
					<div class="container">
						<div class="row align-items-center">
							<div class="col-sm-4">{{ __('Registered ID:') }}</div>
							<div class="col-sm-8 fw-bold">{{ $ext_ids[strtolower($provider)] }}</div>
						</div>
					</div>
					</li>
					@endif
				@else 
					@php
						$avail = true;
					@endphp
				@endif
				@endforeach
			</ul>
<!-- END card-body 
		</div>
-->
	</div>
	@endif

	@if ($avail or empty($ext_ids))
	<div class="border-bottom border-2 border-primary mt-5">
			<h5 class="">{{ __('External identity providers') }}</h5>
			<p>{{ __('These external identity providers are available:') }}</p>
	</div>

	<div class="card mt-4">
			<ul class = "list-group" aclass="list-group mt-2">
				@foreach ($providers as $provider)
				@if (!array_key_exists(strtolower($provider), $ext_ids) || is_array($ext_ids[strtolower($provider)]))
				<li class="list-group-item p-3">
					<div class="d-flex flex-row justify-content-between">
						<div class="text-center" style="width: 64px"><i class="fa fa-user-lock fa-3x"></i></div>
						<div class="flex-grow-1 ps-2">
							<a class="stretch-link btn btn-secondary btn-lg float-end ms-2" href="{{ route('ext.login.ext', [ 'client' => $provider ]) }}" role="button">
								<i class="fa fa-plus me-2"></i>{{ __('Add external ID') }}</a>
							<h5>{{ __($provider) }}</h5>
							<p>{{ __($provider . "-desc") }}</p>
						</div>
					</div>					
				</li>
				@endif
				@endforeach
			</ul>
	</div>
	@endif
	
@endauth

</div>

@endsection