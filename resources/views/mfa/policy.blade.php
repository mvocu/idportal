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
			<h4 class="card-title">{{ __('Multifactor authentication') }}</h4>
			<div class="">
				<p>{{ __('Please select when you want to use second factor during authentication:') }}</p>
				
				<form method="POST" class="container form-horizontal" action="{{ route('mfa.policy.set') }}">
					@csrf
					
					<ul class="list-group mb-4">
						@foreach ($policy->getDescriptions() as $key => $value)
						<li class="list-group-item p-3">
							<div class="form-check">
								<input class="form-check-input" type="radio" name="policy" id="policy-{{ $key }}" value="{{ $key }}" {{ ($policy->getValue() == $key) ?  'checked' : '' }}/>
								<label class="form-check-label" for="policy-{{ $key }}"><strong>{{ __('mfa.policy-' . $key) }}</strong></label>
							</div>
							<div class="p-2 ms-3">
								{{ __($value) }}
							</div>
						</li>
						@endforeach
					</ul>

					<div class="container form-outline mb-4">
						<div class="d-flex flex-row justify-content-between">
							<a class="btn btn-default col-md-3" href="{{ route('mfa.home') }}"><i class="fa fa-arrow-left me-2"></i>{{ __('Back') }}</a>
							<button type="submit" class="col-md-3 btn btn-primary"><i class="fa fa-check me-2"></i>{{ __('Submit') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div> 
	</div>

</div>

@endsection