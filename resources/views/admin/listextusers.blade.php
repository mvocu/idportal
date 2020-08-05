@extends('layouts.app')

@push('scripts')
<script defer>
appMountedHooks = [
		function(vue) {
			vue.$set(vue.extensionObject, 'source', '{{ empty($source) ? (empty(old('source')) ? 'all' : old('source')) : $source }}' );
		}
];
if(vmApp) {
   appMountedHooks.forEach(item => item(vmApp));
}
</script>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="panel">
				<div class="panel-heading">{{ __('List of external accounts') }}</div>

				<div class="panel-body shadow-sm">

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

					<form method="POST" action="{{ route('admin.userext.list.search') }}" aria-label="{{ __('Search external users') }}">
						{{ csrf_field() }}
						
						<div class="row">

							<div class="col-md-10">
								<div class="row form-group">
									<label for="missing"  class="col-md-3 col-form-label text-md-right">{{ __('Only missing') }}:</label>
									<div class="col-md-1">
        	                        	<input id="missing" type="checkbox" class="" name="missing" value="missing" {{ 'missing' == old('missing') ? 'checked' : '' }} />
									</div>
								</div>
								<div class="row form-group">
									<label for="search"  class="col-md-3 col-form-label text-md-right">{{ __('Attribute value') }}:</label>
									<div class="col-md-6">
        		                        <input id="search" type="text" class="form-control" name="search" value="{{ old('search') }}" />
									</div>
								</div>
								<div class="row form-group">
									<label for="source" class="col-md-3 col-form-label text-md-right">{{ __('External source') }}:</label>
									<div class="col-md-6">
										<select name="source" v-model="extensionObject.source">
											<option value="all">All</option>
											@foreach($sources as $source)
											<option value="{{ $source->id }}">{{ $source->name }}</option>
											@endforeach
										</select>
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
												
					<form method="POST" action="{{ route('admin.userext.synchronize') }}" aria-label="{{ __('Synchronize external sources') }}">
						{{ csrf_field() }}
						
						<input type="hidden" name="source" v-bind:value="extensionObject.source" />

						<div class="row form-group" v-if="extensionObject.source != 'all'">
							<div class="col-md-10">
								<div class="row">
									<div class="col-md-9 offset-md-3 col-md-offset-3">
										<button type="submit" class="btn btn-primary">{{ __('Synchronize') }}</button>
									</div>
								</div>
							</div>
						</div>
					</form>

				</div>
				<div class="panel-body">
					{{ $table->render() }}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
