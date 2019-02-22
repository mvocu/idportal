@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Dashboard') }} @if (isset($user)) - {{ $user->name }} @endif</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ( isset($user) )
                    	<div class="row">
                    		<div class="col-xs-4 text-right">{{ __('Login') }}</div>
                    		<div class="col-xs-8">{{ implode(",", $user->uid) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4 text-right">{{ __('E-mail') }}</div>
                    		<div class="col-xs-8">{{ empty($user->getEmail()) ? "" : implode(";", $user->mail) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4 text-right">{{ __('Phone') }}</div>
                    		<div class="col-xs-8">{{ empty($user->getTelephoneNumber()) ? "" : implode("; ", $user->telephonenumber) }}</div>
                    	</div>
					@foreach ($accounts as $account)
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __($account['name']) }}</div>
                                <div class="col-xs-2">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "ne" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-xs-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-xs-3">mail {{ empty($account['email']) ? "" : $account['email'] }}</div>
					@endif
                        </div>
					@endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
