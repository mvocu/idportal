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

		<div class="card-header">
			<h4 class="card-title">{{ __('Add external identity') }}</h4>
			<p class="alert alert-info">
				<i class="fa fa-info me-2"></i>
				{{ __('You are about to add an external identity to your CAS account, which will allow this external identity to login using your CAS account.') }}
			</p>
	    </div>
	    
	    <div class="card-body">

			<div class="d-flex flex-row align-items-start">
				<div class="col-sm-4">
					<span>{{ __('Identity in CAS') }}</span>
				</div>
				<div class="col-sm-8">
					<span>{{ __('Identity from external provider') }}</span>
				</div>
			</div>
			<div class="d-flex flex-row align-items-start mt-1">
				<div class="col-sm-4">
					<span class="fw-bold">{{ $local }}</spanp>
				</div>
	      		<div class="col-sm-8">
					<span class="fw-bold">{{ $remote }}</span>
				</div>
			</div>
			
			<div class="d-flex flex-row justify-content-end align-items-start mt-4">
				<form method="POST"  action="{{ route('ext.add', [ 'provider' => $provider ] ); }}">
					@csrf
					<input type="hidden" name="local" value="{{$local}}"/>
					<input type="hidden" name="remote" value="{{$remote}}"/>
					<button class="btn btn-danger" role="button" type="submit"><i class="fa fa-plus me-2"></i>{{ __('Confirm') }}</button>
				</form>	
			</div>
		</div> 

		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('ext.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

	</div>

</div>

@endsection