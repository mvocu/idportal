@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="panel">
				<div class="panel-heading">{{ __('List of users') }}</div>

				<div class="panel-body shadow-sm" style="border-bottom: 1px solid #ccc;">
					<form method="POST" action="{{ route('admin.user.list.search') }}" aria-label="{{ __('Search users') }}">
						{{ csrf_field() }}
						
						<div class="row">

							<div class="col-md-10">
								<div class="row form-group">
									<label for="search"  class="col-md-3 col-form-label text-md-right">{{ __('Attribute') }}:</label>
									<div class="col-md-6">
        		                        <input id="search" type="text" class="form-control" name="search" value="{{ old('search') }}" />
									</div>
								</div>
								<div class="row form-group">
									<label for="internal" class="col-md-3 col-form-label text-md-right">{{ __('No external account') }}:</label>
									<div class="col-md-6">
								    	<input id="internal" type="checkbox" class="" name="internal" value="internal" {{ 'internal' == old('internal') ? 'checked' : '' }} />
									</div>
								</div>
								<div class="row form-group">
									<label for="voting" class="col-md-3 col-form-label text-md-right">{{ __('With voting code') }}:</label>
									<div class="col-md-6">
								    	<input id="voting" type="checkbox" class="" name="voting" value="voter" {{ 'voter' == old('voting') ? 'checked' : '' }} />
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
				
				<div class="panel-body">
					{{ $table->render() }}
				</div>
				
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 text-center">
							<a class="btn btn-primary" href="{{ route('admin.user.new') }}">{{ __('Add user') }}</a> 
							<a class="btn btn-primary" href="{{ route('admin.userext.list') }}">{{ __('List external') }}</a> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
			
