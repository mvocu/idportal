@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __($ext_source->name) }} - {{ __('User detail') }}</div>

                <div class="card-body">
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
                        @csrf

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
                        

					    @if (!$editable->isEmpty())
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }} 
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>

              	</div>
              	
              	<div class="card-body">
                        <div class="form-group row border-bottom">
                                <div class="col-4 text-right font-weight-bold">{{ __('Attribute') }}</div>
                                <div class="col-8 font-weight-bold">{{ __('Value') }}</div>
						</div>						
                        <div class="row">
                                <div class="col-4 text-right">ID</div>
                                <div class="col-8">{{ $user_ext->login }}</div>
						</div>						
					@foreach ($attributes as $name => $value)
                        <div class="row">
                                <div class="col-4 text-right">{{ __($name) }}</div>
                                <div class="col-8">{{ $value }}</div>
						</div>						
					@endforeach
              	</div>

            	<div class="card-footer">
					<div class="row">
						<div class="col-2"><a href="{{ route('home') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
