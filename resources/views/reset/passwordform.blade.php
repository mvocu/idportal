@extends('layouts.app')

@section('header')
<li class="">
	<a href="{{ route('reset.methods') }}" class="btn btn-floating me-4"><i class="fas fa-arrow-left"></i></a>
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

	<div class="border-bottom border-2 border-primary mt-5">
			<h5 class="">{{ __('Set new password') }}</h5>
	</div>

	<div class="card mt-5">
			
		<div class="card-body">
			<h5 class="mb-3">{{ __('New password') }}</h5>
			<div class="row g-4">
			<div class="col-sm-6">
		    <form class="h-100" method="POST" action="{{ route('reset.change') }}" aria-label="{{ __('New password') }}">
			@csrf
			<div class="row g-3">
				<div class="col-sm-12">
					<x-input type="password" name="password" value="" :errors="$errors" 
						text="Password" required="true" />
            	</div>
				<div class="col-sm-12">
					<x-input type="password" name="password2" value="" :errors="$errors" 
						text="Password (again)" required="true" />
            	</div>
			</div>
            <div class="text-center mt-4">
				<button type="submit" class="btn btn-primary mx-auto">{{ __('Submit') }}</button>
			</div>
			</form>
			</div>
			<div class="col-sm-6">
			</div>
			</div>
		</div>			
	</div>			


</div>

@endsection