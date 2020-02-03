@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel">
				<div class="panel-body">
					@include('admin.part.userextdetail', ['user' => $user])
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
