@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard @if (isset($user)) - {{ $user->name }} @endif</div>

                <div class="card-body">
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

                    @if ( isset($user) )
                    	<div class="row">
                    		<div class="col-4 text-right">{{ __('Login') }}</div>
                    		<div class="col-8">{{ implode(",", $user->uid) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-4 text-right">{{ __('E-mail') }}</div>
                    		<div class="col-8">{{ empty($user->getEmail()) ? "" : implode(",", $user->mail) }}</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-4 text-right">{{ __('Phone') }}</div>
                    		<div class="col-8">{{ empty($user->getTelephoneNumber()) ? "" : implode(",", $user->telephonenumber) }}</div>
                    	</div>
              	</div>
                <div class="card-body">
					@foreach ($accounts as $account_id => $account)
					@if (!$account['idp'])
                        <div class="row">
                                <div class="col-4 text-right">{{ __($account['name']) }}</div>
                                <div class="col-1">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "ne" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-3">mail {{ empty($account['email']) ? "" : $account['email'] }}</div>
						@if ($account['editable'])
								<div class="col-1"><a href="{{ route('ext.account.show', [ 'user' => $user->getDatabaseUser(), 'source' => $account_id ]) }}" class="pull-right btn btn-default btn-sm btn-small"><span class="fa fa-pencil">&nbsp;</span></a></div>
						@endif
					@else
						@if ($account['editable'])
								<div class="col-7"><a href="{{ route('ext.account.add', [ 'user' => $user->getDatabaseUser(), 'source' => $account_id ]) }}" class="pull-right btn btn-default btn-sm btn-small"><span class="fa fa-plus">&nbsp;</span></a></div>
						@endif
					
					@endif
                        </div>
                    @endif
					@endforeach
                </div>
                
                <div class="card-body">
					@foreach ($accounts as $account_id => $account)
					@if ($account['idp'])
                        <div class="row">
                                <div class="col-4 text-right">{{ __($account['name']) }}</div>
                                <div class="col-1">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-3">mail {{ empty($account['email']) ? "" : $account['email'] }}</div>
								<div class="col-1"><a href="{{ route('remove.oidc', [ 'user_ext' =>  $account['user_ext'] ] ) }}" class="pull-right btn btn-default"><span class="fa fa-cross">&nbsp;</span></a></div>
					@else
								<div class="col-7"><a href="{{ route('register.oidc', [ 'client' => $account['name'] ] ) }}" class="btn btn-social"><span class="fa fa-openid fa-{{ $account['name'] }}">&nbsp;</span>{{ $account['name'] }} - {{ __("Add identity") }} </a></div>
					@endif
                        </div>
                    @endif
					@endforeach

					@endif
                </div>

            	<div class="card-footer">
					<div class="row">
						<div class="col-4"><a href="" class="btn btn-primary btn-block">{{ __('Change password') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
