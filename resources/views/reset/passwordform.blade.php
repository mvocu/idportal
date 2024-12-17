@extends('layouts.app')

@section('header')
<li class="">
	<a href="" class="btn btn-floating me-4"><i class="fas fa-arrow-left"></i></a>
</li>
<li>
	<span>{{ __('Change password for CAS') }}</span>
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


	<div class="d-flex flex-sm-row flex-column flex-wrap align-items-stretch justify-content-between mt-5">
		<div class="col-sm-5 mb-2">
		    <form class="h-100" method="POST" action="{{ route('reset.search') }}" aria-label="{{ __('Search form') }}">
			@csrf
			</form>
		</div>
	</div>
	</form>

</div>

@endsection