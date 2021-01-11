@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ __('Registration of external identity') }}: {{ $idp }}</div>

				<div class="panel-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

					@if ($invalid->has('failure'))
					    <div class="alert alert-danger" role="alert">
    						{{ $invalid->first('failure') }}
    					</div>
					@endif

					@if (isset($user))
                    <form method="POST" action="{{ route('register.eidp.add', [ 'client' => $idp ]) }}" aria-label="{{ __('Add identity') }}">
					@else
                    <form method="POST" action="{{ route('register.eidp.create', [ 'client' => $idp ]) }}" aria-label="{{ __('Register') }}">
                    @endif
                        {{ csrf_field() }}

						@foreach ($attributes->sortBy('order') as $name => $value) 
						@if (!empty($value['display']))
                        <div class="form-group row mb-0 {{ $invalid->has($name) ? 'has-error' : '' }}">
                                <div class="col-xs-4 text-right">{{ $value['display'] }}</div>
								<div class="col-xs-8">{{ $value['value'] }}</div>
                                @if ($invalid->has($name))
                                    <span class="help-block" role="alert">
                                        <strong>{{ $invalid->first($name) }}</strong>
                                    </span>
                                @endif
								
						</div>
						@endif
						@endforeach
						
						@if (isset($user) or $invalid->isEmpty())
                        <div class="form-group row mb-0 mt-4">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($user) ? __('Add identity') : __('Register') }}
                                </button>
                            </div>
                        </div>
                        @endif
                        
                   	</form>

				</div>

            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-2"><a href="{{ route('register') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
						<div class="col-xs-2"><a href="{{ route('eidp.logout', [ 'client' => $idp ]) }}" class="btn btn-secondary" aria-label="{{ __('Use another identity') }}">{{ __('Use another identity') }}</a></div>
					</div>
            	</div>
				
			</div>
		</div>
	</div>
</div>


@endsection
                
