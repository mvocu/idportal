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
			<h5 class="card-title">{{ __('Name security key') }}</h5>
			<p>{{ __('mfa.webauthn.choosename') }}</p> 
		</div>
		
		<div class="card-body py-1">
			<div class="d-flex flex-row justify-content-center align-items-start my-4">
					<form method="POST" class="" action="https://{{ env('OIDC_SERVER') }}/cas/webauthn/register">
						<button class="btn btn-primary" type="submit"><i class="fa fa-plus-circle me-2"></i>{{ __('Register') }}</button>
					</form>
			</div>
		</div>
		
		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('mfa.webauthn') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

	</div>
	
</div>

@endsection