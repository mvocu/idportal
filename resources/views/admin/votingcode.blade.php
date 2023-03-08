@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Voting codes') }}</div>

				<div class="panel-body">
					<div class="row">
						<div class="col-xs-4">
							<strong>{{ __('Full name') }}:</strong>
						</div>
						<div class="col-xs-8">
								{{ $user->first_name }} {{ $user->last_name }}
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
							<strong>{{ __('birth_date') }}:</strong>
						</div>
						<div class="col-xs-8">
							{{ $user->birth_date }}
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4">
							<strong>{{ __('ID card') }}:</strong>
						</div>
						<div class="col-xs-8">
							{{ $idcard }}
						</div>
					</div>
				</div>
				
				<div class="panel-body mt-2">
					<div class="row">
						<div class="col-xs-10 col-xs-offset-1 text-center">
							{{ __('You have been assigned the following voting code:') }}
						</div>
					</div>				

					<div class="row" style="padding-top: 2rem; padding-bottom: 2rem">
						@if (!empty($code))
						<div class="col-xs-10 col-xs-offset-1 text-center">
							<h2>{{ $code->code }}</h2>
						</div>
						@endif
					</div>				

					<div class="row" style="margin-bottom: 2rem">
						<div class="col-xs-10 col-xs-offset-1 text-center">
							{{ __('You can use this code for voting in participative budget') }} <a href="">{{ __('here') }}.</a>
						</div>
					</div>
				</div>				

            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 text-center">
							<a class="btn btn-primary" href="{{ route('admin.user.new') }}">{{ __('Add user') }}</a> 
							<a class="btn btn-primary" href="{{ route('admin.user.list') }}">{{ __('List users') }}</a> 
							<a class="btn btn-primary" href="{{ route('admin.userext.list') }}">{{ __('List external') }}</a> 
						</div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection