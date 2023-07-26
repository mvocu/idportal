@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Account status') }}</div>

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

					@if (!$locked && $pwlock === false)
					<div class="alert alert-success">
						{{ __('This account is ACTIVE.') }}
					</div>
					@elseif ($locked)
					<div class="alert alert-danger">
						<p>{{ __('This account is LOCKED.') }}</p>
						@if ($consent)
						<p>{{ __('Please contact administrators at address') }} <a href="mailto:{{ config('mail.support', '') }}">{{ config('mail.support', '') }}</a></p>
						@else
						<p>{{ __('The consent with terms of usage has expired and needs to be renewed.') }}</p>
						@endif	
					</div>
					<div class="row">
						<div class="col-xs-2 col-xs-offset-8">
							<a class="btn btn-primary" href="{{ route('password.request', [ 'uid' => $user ] ) }}">{{ __('Reset account') }}</a>
						</div>
					</div>
					@else 
					<div class="alert alert-danger">
						<p>{{ __('This account is locked temporarily due to too many failed login attempts.') }}</p>
						<p>{{ __('Account will be unlocked on :time.', ['time' => $pwlock ]) }}</p> 
					</div>
					@endif
					
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