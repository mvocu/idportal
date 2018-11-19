@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ( $user )
                    	<div class="row">
                    		<div class="col-xs-12">{{ $user->name }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4"></div>
                    		<div class="col-xs-8">{{ $user->uid }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4"></div>
                    		<div class="col-xs-8">{{ $user->mail }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4"></div>
                    		<div class="col-xs-8">{{ $user->telephonenumber }}</div>
                    	</div>
                    
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
