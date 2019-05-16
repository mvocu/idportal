@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
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
                    		<div class="col-xs-3 text-right">{{ __('Login') }}</div>
                    		<div class="col-xs-9">{{ implode(",", $user->uid) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-3 text-right">{{ __('E-mail') }}</div>
                    		<div class="col-xs-9">{{ empty($user->getEmail()) ? "" : implode(";", $user->mail) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-xs-3 text-right">{{ __('Phone') }}</div>
                    		<div class="col-xs-9">{{ empty($user->getTelephoneNumber()) ? "" : implode("; ", $user->telephonenumber) }}</div>
                    	</div>
              	</div>

                <div class="panel-body">
					@foreach ($accounts as $account_id => $account)
                        <div class="row">
                                <div class="col-xs-3 text-right">{{ __($account['name']) }}</div>
                                <div class="col-xs-1">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "ne" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-xs-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-xs-4">mail: {{ empty($account['email']) ? "" : $account['email'] }}</div>
						@if ($account['editable'])
								<div class="col-xs-1"><a class="pull-right btn btn-default btn-sm btn-small"><span class="fa fa-pencil">&nbsp;</span></a></div>
						@endif
					@else
						@if ($account['editable'])
						@endif
					@endif
                        </div>
					@endforeach
                    @endif
                </div>
                
            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-4"><a href="" class="btn btn-primary btn-block">{{ __('Change password') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
