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

                    @if ( isset($user) )
                    	<div class="row">
                    		<div class="col-xs-12">{{ $user->name }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4">{{ __('Login') }}</div>
                    		<div class="col-xs-8">{{ implode(",", $user->uid) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4">{{ __('E-mail') }}</div>
                    		<div class="col-xs-8">{{ empty($user->getEmail()) ? "" : implode(",", $user->mail) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4">{{ __('Phone') }}</div>
                    		<div class="col-xs-8">{{ empty($user->getTelephoneNumber()) ? "" : implode(",", $user->telephonenumber) }}</div>
                    	</div>
                    
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
