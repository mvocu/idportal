@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel">
            	<div class="panel-heading">{{ __('New user') }}</div>
            	
				<div class="panel-body">
                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('admin.user.create') }}">
                        {{ csrf_field() }}

                        <div class="form-group row {{ $errors->has('firstname') ? ' has-error' : '' }}">
                            <label for="firstname" class="col-md-4 col-form-label text-md-right control-label">{{ __('First name') }}</label>

                            <div class="col-md-6">
                                <input id="firstname" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}" required autofocus>

                                @if ($errors->has('firstname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('firstname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('lastname') ? ' has-error' : '' }}">
                            <label for="lastname" class="col-md-4 col-form-label text-md-right control-label">{{ __('Last name') }}</label>

                            <div class="col-md-6">
                                <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}" required autofocus>

                                @if ($errors->has('lastname'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('lastname') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('birthdate') ? ' has-error' : '' }}">
                            <label for="birth_year" class="col-md-4 col-form-label text-md-right control-label">{{ __('Year of birth') }}</label>

                            <div class="col-md-6">
                                <input id="birth_year" type="text" class="form-control" name="birth_year" value="{{ old('birth_year') }}" required>

                                @if ($errors->has('birth_year'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('birth_year') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						@if (0)
                        <div class="form-group{{ $errors->has('idcard') ? ' has-error' : '' }}">
                            <label for="idcard" class="col-md-4 col-form-label text-md-right control-label"><em>{{ __('Identity card number') }}</em></label>

                            <div class="col-md-6">
                                <input id="idcard" type="text" class="form-control" name="idcard" value="{{ old('idcard') }}" required>

                                @if ($errors->has('idcard'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('idcard') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="conflicts" class="col-md-4 col-form-label text-md-right control-label">{{ __('Ignore conflicts') }}</label>

                            <div class="col-md-1">
                                <input id="conflicts" type="checkbox" class="form-control" name="conflicts" value="ignore" {{ old('conflicts') == 'ignore' ? 'checked' : '' }} /> 
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Create') }}
                                </button>
                            </div>
                        </div>
                    </form>
				</div>
								
				@if (!empty($table)) 
				<div class="panel-body">
					<h4>{{ __('Found similar users') }}</h4>
					<hr/>
					{{ $table->render() }}
				</div>
				@endif
				
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-2"><a href="{{ url()->previous() }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
