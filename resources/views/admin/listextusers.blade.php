@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
				<div class="card-body shadow-sm">
					<form method="POST" action="{{ route('admin.userext.list.search') }}" aria-label="{{ __('Search external users') }}">
						@csrf
						
						<div class="row form-group">
							<label for="missing"  class="col-md-2 col-form-label text-md-right">{{ __('Only missing') }}:</label>

							<div class="col-md-1">
                                <input id="missing" type="checkbox" class="" name="missing" value="missing" />
							</div>

							
							<label for="search"  class="col-md-2 col-form-label text-md-right">{{ __('Attribute value') }}:</label>
							
							<div class="col-md-5">
                                <input id="search" type="text" class="form-control" name="search" value="{{ old('search') }}" />
							</div>
							
							<div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Search') }} 
                                </button>
							</div>
						</div>
					</form> 
				</div>
				<div class="card-body">
					{{ $table->render() }}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
