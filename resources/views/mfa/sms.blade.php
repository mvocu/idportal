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
			<h5 class="card-title">{{ __('SMS message') }}</h5>
			<p>{{ __('mfa.sms-description') }}</p> 
		</div>
		
		@if ( !empty($sms) )
		<div class="card-body py-1">
			<h6 class="fw-bold">{{ $sms }}</h6>
			<p>{{ __('Verification codes will be sent by SMS to this number.') }}</p>
		</div>
		
		<div class="card-body">
			<div class="d-flex flex-row justify-content-end align-items-start">
					<a class="btn btn-info" role="button" href="{{ route('mfa.sms.test') }}"><i class="fa fa-question me-2"></i>{{ __('Test') }}</a>
			</div>
		</div>
		
		@else

		<div class="card-body py-1">
			<div class="alert alert-info text-align-center">
			<i class="fa fa-info me-2"></i>
			{{ __('You have no registered phone number for text messages.') }}
			</div>
		</div>
		
		@endif
		
		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('mfa.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

	</div>
	
</div>

@endsection