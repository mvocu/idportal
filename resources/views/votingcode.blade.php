@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Voting codes') }}</div>

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
              	</div>

				<div class="row">
					<div class="col-xs-10 col-xs-offset-1 text-center">
						{{ __('You have been assigned the following voting code:') }}
					</div>
				</div>				

				<div class="row" style="padding-top: 2rem; padding-bottom: 2rem">
					<div class="col-xs-10 col-xs-offset-1 text-center">
						<h2>{{ $code->code }}</h2>
					</div>
				</div>				

				<div class="row" style="margin-bottom: 2rem">
					<div class="col-xs-10 col-xs-offset-1 text-center">
						{{ __('You can use this code for voting in participative budget') }} <a href="">{{ __('here') }}.</a>
					</div>
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
                    