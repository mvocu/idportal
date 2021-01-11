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

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('failure') }}
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
					@if (!$account['idp'])
                        <div class="row">
                                <div class="col-xs-3 text-right">{{ __($account['name']) }}</div>
                                <div class="col-xs-1">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "ne" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-xs-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-xs-4">mail: {{ empty($account['email']) ? "" : $account['email'] }}</div>
								<div class="col-xs-1">
									<a href="{{ route('ext.account.show', [ 'user' => $user->getDatabaseUser(), 'source' => $account_id ]) }}" class="pull-right btn btn-default btn-sm btn-small">
						@if ($account['editable'])
										<span class="fa fa-pencil">&nbsp;</span>
						@else 
										<span class="fa fa-search-plus">&nbsp;</span>
						@endif
									</a>
								</div>
					@else
						@if ($account['creatable'])
								<div class="col-xs-8"><a href="{{ route('ext.account.ask', [ 'user' => $user->getDatabaseUser(), 'source' => $account_id ]) }}" class="pull-right btn btn-default btn-sm btn-small"><span class="fa fa-plus">&nbsp;</span></a></div>
						@endif
					@endif
                        </div>
                    @endif
					@endforeach
                </div>
                
                <div class="panel-body">
					@foreach ($accounts as $account_id => $account)
					@if ($account['idp'])
                        <div class="row">
                                <div class="col-xs-3 text-right">{{ __($account['name']) }}</div>
                                <div class="col-xs-1">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-xs-3">tel. {{ empty($account['phone']) ? "" : $account['phone'] }}</div>
                                <div class="col-xs-4">mail: {{ empty($account['email']) ? "" : $account['email'] }}</div>
					@if (isset($account['user_ext']))
								<div class="col-xs-1">
									<a href="{{ route('ext.account.remove', [ 'user_ext' =>  $account['user_ext'] ] ) }}" class="pull-right btn btn-sm btn-small btn-danger"><span class="fa fa-remove">&nbsp;</span></a>
									<a href="{{ route('ext.account.show', [ 'user' => $user->getDatabaseUser(), 'source' => $account_id ] ) }}" class="pull-right btn btn-sm btn-small btn-default"><span class="fa fa-search-plus">&nbsp;</span></a>
								</div>
					@endif
					@else
								<div class="col-xs-8"><a href="{{ route('register.eidp', [ 'client' => $account['name'] ] ) }}" class="btn btn-social"><span class="fa fa-openid fa-{{ $account['name'] }}">&nbsp;</span>{{ $account['name'] }} - {{ __("Add identity") }} </a></div>
					@endif
                        </div>
                    @endif
					@endforeach

					@endif
                </div>

            	<div class="panel-footer">
<!--
					<div class="row">
						<div class="col-xs-4"><a href="{{ route('password.change') }}" class="btn btn-primary btn-block">{{ __('Change password') }}</a></div>
					</div>
-->
            	</div>

            </div>
        </div>
    </div>
    
	@if (isset($children))
    <div class="row justify-content-center mt-2">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">{{ __('Managed accounts') }}</div>
				
				<div class="panel-body">
				@foreach ($children as $child)
					<div class="row form-group">
						<div class="col-xs-2">{{ $child->getFirstAttribute('uid') }}</div>
						<div class="col-xs-3">{{ $child->getCommonName() }}</div>
						<div class="col-xs-3"></div>
						<div class="col-xs-3">
							<a href="{{ route('password.change', ['target' => $child ]) }}" class="btn btn-primary btn-block">{{ __('Change password') }}</a>
						</div>
					</div>
				@endforeach
				</div>
				
			</div>
		</div>
    </div>
    @endif
    
</div>
@endsection
