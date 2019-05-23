@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __($ext_source->name) }} - {{ __('User detail') }}</div>

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

                    <form method="POST" action="{{ route('ext.account.modify', [ 'user_ext' => $user_ext, 'action' => $action ]) }}" aria-label="{{ __('Update contacts') }}">
                        {{ csrf_field() }}

						@foreach ($editable as $attrdef)
                        <div class="form-group row{{ $errors->has($attrdef->name) ? ' has-error' : '' }}">
                            <label for="{{ $attrdef->name }}" class="col-md-4 control-label col-form-label text-right">{{ $attrdef->display_name }}</label>

                            <div class="col-md-6">
                                <input id="{{ $attrdef->name }}" type="text" class="form-control" name="{{ $attrdef->name }}" value="{{ old($attrdef->name) }}" required>

                                @if ($errors->has($attrdef->name))
                                    <span class="help-block" role="alert">
                                        <strong>{{ $errors->first($attrdef->name) }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        

                        <div class="form-group row mb-0">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }} 
                                </button>
                            </div>
                        </div>
                    </form>

              	</div>
              	
              	<div class="panel-body">
                        <div class="form-group row border-bottom" style="border-bottom: 1px solid lightgrey">
                                <div class="col-md-4 text-right font-weight-bold">{{ __('Attribute') }}</div>
                                <div class="col-md-8 font-weight-bold">{{ __('Value') }}</div>
						</div>						
					@foreach ($attributes as $name => $value)
                        <div class="row">
                                <div class="col-md-4 text-right">{{ __($name) }}</div>
                                <div class="col-md-8">{{ $value }}</div>
						</div>						
					@endforeach
              	</div>

            	<div class="panel-footer">
					<div class="row">
						<div class="col-md-2"><a href="{{ route('home') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
