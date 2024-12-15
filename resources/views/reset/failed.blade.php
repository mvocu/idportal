@extends('layouts.app')

@section('header')
<li class="">
	<a href="" class="btn btn-floating me-4"><i class="fas fa-arrow-left"></i></a>
</li>
<li>
	<span>{{ __('Getting new password for CAS') }}</span>
</li>
@endsection

@section('content')

<div class="d-flex flex-column col-lg-7" style="max-width: 720px">	

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

	<div class="card d-flex flex-column align-items-center mt-5">
		<h4 class="card-title mt-4">{{ __('Identity verification failed') }}</h4>
		<p class="alert alert-info mt-2">
			<i class="fa fa-info me-2"></i>
			{{ __('The identity proof you presented does not correspond with the data in your account.') }}
		</p>

		<div class="card-body col-sm-11">
			<h5>{{ __('Information about external account') }}</h5>			
			<x-identity :identity="$identity" />
		</div>

		<div class="card-body col-sm-12 text-center">
			<a class="btn btn-default" href="{{ route('reset.home') }}">
				<i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}
			</a>
			@auth
			<a class="btn btn-primary" role="button" href="{{ route('logout') }}">
				<i class="fa fa-lock me-2"></i>{{ __('Logout') }}
			</a>
			@endauth
		</div>				
	</div>

</div>

@endsection