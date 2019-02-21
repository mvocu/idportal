@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard @if (isset($user)) - {{ $user->name }} @endif</div>

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
                    		<div class="col-xs-8">{{ empty($user->getEmail()) ? "" : implode(",", $user->mail) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-4 text-right">{{ __('Phone') }}</div>
                    		<div class="col-xs-8">{{ empty($user->getTelephoneNumber()) ? "" : implode(",", $user->telephonenumber) }}</div>
                    	</div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('SMS info') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-sms-info')) ? "ne" : "ano" }}</div>
                        </div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('Newsletter') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-mail-contacts')) ? "ne" : "ano" }}</div>
                        </div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('Helios Energo') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-helios-energo')) ? "ne" : "ano" }}</div>
                        </div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('Ginis') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-ginis')) ? "ne" : "ano" }}</div>
                        </div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('Clavius') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-clavius')) ? "ne" : "ano" }}</div>
                        </div>
                        <div class="row">
                                <div class="col-xs-4 text-right">{{ __('AD Uvaly') }}</div>
                                <div class="col-xs-8">{{ empty($value = $user->getFirstAttribute('employeenumber;x-ad-meu-uvaly')) ? "ne" : "ano" }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
