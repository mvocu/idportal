@extends('layouts.app')

@section('header')
<li class="nav-item">
	<a href="" class="btn btn-floating me-4"><i class="fas fa-arrow-left"></i></a>
</li>
<li class="navbar-text">
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
			<h5 class="">{{ __('Verify your identity') }}</h5>
			<p>{{ __('Choose the most appropriate identification method:') }}</p>
	</div>


	<div class="d-flex flex-sm-row flex-column flex-wrap align-items-stretch justify-content-around mt-5">
		@foreach ($methods as $method)
		<div class="col-sm-3 mb-5 me-2" style="min-width: 175px">
			<div class="card d-flex flex-column align-items-stretch h-100">
			<div class="bg-image text-center">
				<img src="{{ asset('images/'.$method.'-logo.png') }}" class="x-card-img-top" alt="{{$method}} logo" />
			</div>					
			<div class="card-body">
				<p class="card-text">
					{{ __($method."-desc") }}
				</p>
			</div>
			<div class="card-footer text-center flex-grow-0">
				<a href="{{ route('reset.verify', [ 'method' => $method ]) }}" class="link-primary">{{ __('Proceed') }}<i class="ms-1 fa fa-arrow-right"></i></a>
			</div>
			</div>
		</div>
		@endforeach
	</div>

</div>

@endsection