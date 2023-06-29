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

		<div class="card-body">
			<h4 class="card-title">{{ __('Reinitialize session') }}</h4>
			<p class="alert alert-info">
				<i class="fa fa-info me-2"></i>
				{{ __('Your old session without updated information about external identity is still active. You need to logout and sign in again to reinitiliaze your session.') }}
			</p>
			
	    	<p class="mt-4">
	    	{{ __("By clicking on 'Continue' you will be redirected to CAS logout.") }}
	    	</p>
			<div class="d-flex flex-row justify-content-end align-items-start mt-2">
				<a class="btn btn-primary" role="button" href="{{ route('logout') }}"><i class="fa fa-lock me-2"></i>{{ __('Continue') }}</a>
			</div>
		</div>

		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('ext.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

    </div>

</div>

@endsection