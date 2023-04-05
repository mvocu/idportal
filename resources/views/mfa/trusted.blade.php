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
			<h5 class="card-title">{{ __('Registered trusted devices') }}</h5>
			<p>{{ __('You will not be asked to use second factor when authenticating from one of those devices.') }}</p>
		</div>
		
		@if ( !empty($trusted) && !$trusted->isEmpty() )
		<div class="card-body py-1">
			<p>{{ __('You have chosen to skip the second factor on the following trusted devices') }}:</p>
			<ul class="list-group">
				@foreach ($trusted as $device)
				<li class="list-group-item py-3">
					<div class="d-flex flex-row align-items-center">
						<div class="col-sm-4 fw-bold">{{ $device->getName() }}</div>
						<div class="col-sm-7">{{ __('Expires at') }} {{ $device->getExpirationDate() }} </div>
						<div class="col-sm-1 text-end">
							<form method="POST" class="" action="{{ route('mfa.trusted.delete.one', [ 'device' => $device->getId() ] ) }}">
								@csrf
								@method('DELETE')
						
								<button class="btn btn-light btn-floating" type="submit"><i class="fa fa-times"></i></button>
							</form>
						</div>
					</div>
				</li> 
				@endforeach
			</ul>

		</div>

		<div class="card-body">
			<div class="d-flex flex-row justify-content-end align-items-start">
					<form method="POST" class="" action="{{ route('mfa.trusted.delete') }}">
						@csrf
						@method('DELETE')
						
						<button class="btn btn-primary" type="submit"><i class="fa fa-times me-2"></i>{{ __('Remove all') }}</button>
					</form>
			</div>
		</div>

		@else
		<div class="card-body py-1">
			<div class="alert alert-info text-align-center">
			<i class="fa fa-info me-2"></i>
			{{ __('You have no registered trusted devices.') }}
			</div>
		</div>
		@endif
				
		
		<div class="card-footer">
			<a class="btn btn-default col-md-3" href="{{ route('mfa.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
		</div>				

	</div>
	
</div>

@endsection