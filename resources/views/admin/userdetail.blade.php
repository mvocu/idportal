@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="panel">
            	<div class="panel-heading">{{ __('User detail - :user', ['user' => $user->first_name . " " . $user->last_name ]) }}</div>
				<div class="panel-body">
					@include('admin.part.userdetail', ['user' => $user, 'id' => $id ])
				</div>
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
