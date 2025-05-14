@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Confirm voter registration') }}</div>

                <div class="panel-body">

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

                    <form class="form-horizontal" method="POST" action="{{ route('voting.confirm') }}" aria-label="{{ __('Confirm voter registration') }}">
                        {{ csrf_field() }}

						@if (empty($token)) 

                        <div class="form-group row {{ $errors->has('token') ? ' has-error' : '' }}">
                            <label for="token" class="col-md-4 col-form-label text-md-right control-label">{{ __('Authorization token') }}</label>

                            <div class="col-md-6">
                                <input id="token" type="text" class="form-control" name="token" value="{{ $token ?? old('token') }}" required autofocus>

                                @if ($errors->has('token'))
                                    <span class="help-block" role="alert">
                                        <strong>{{ $errors->first('token') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						@else
						
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        @endif


                        <div class="form-group row mb-0">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirm') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-2"><a href="{{ route('voting.home') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
            	</div>
                
            </div>
        </div>
    </div>
</div>
@endsection
