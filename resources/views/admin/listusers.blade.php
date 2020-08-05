@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
				<div class="card-header">{{ __('List of users') }}</div>

				<div class="card-body shadow-sm">
					<form method="POST" action="{{ route('admin.user.list.search') }}" aria-label="{{ __('Search users') }}">
						@csrf
						
						<div class="row">

							<div class="col-md-10">
								<div class="row form-group">
									<label for="search"  class="col-md-3 col-form-label text-md-right">{{ __('Attribute') }}:</label>
									<div class="col-md-6">
        		                        <input id="search" type="text" class="form-control" name="search" value="{{ old('search') }}" />
									</div>
								</div>
							</div>

							<div class="col-md-2">
								<div class="row form-group">
									<div class="col-md-12">
	                                <button type="submit" class="btn btn-primary">
                                    	{{ __('Search') }} 
                                	</button>
                                	</div>
								</div>                                	
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
			