@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __($ext_source->name) }} - {{ __('Ask for new account') }}</div>

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

					<div class="row mb-4">
						<div class="col-12 col-md-12">
							@if (!$ext_source->editable)
							{{ __("account.ext.submission", [ 'source' => __($ext_source->name) ]) }}
							@endif
						</div>
					</div>
					
                    <form method="POST" action="{{ route('ext.account.add', ['user' => $user, 'source' => $ext_source]) }}" aria-label="{{ __('Add user') }}">
                        {{ csrf_field() }}

						@foreach ($editable as $attrdef)
                        <div class="form-group row">
                            <label for="{{ $attrdef->name }}" class="col-md-4 col-form-label text-md-right">{{ $attrdef->display_name }}</label>

                            <div class="col-md-6">
                                <input id="{{ $attrdef->name }}" type="text" class="form-control{{ $errors->has($attrdef->name) ? ' is-invalid' : '' }}" name="{{ $attrdef->name }}" value="{{ old($attrdef->name) }}" required>

                                @if ($errors->has($attrdef->name))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first($attrdef->name) }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach

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
						<div class="col-xs-2"><a href="{{ route('home') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
