@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
				<div class="card-body">
					@include('admin.part.userextdetail', ['user' => $user])
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
