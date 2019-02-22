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
					@foreach ($accounts as $account)
                        <div class="row">
                                <div class="col-4 text-right">{{ __($account['name']) }}</div>
                                <div class="col-2">{{ empty($value = $user->getFirstAttribute('employeenumber;x-'.$account['tag'])) ? "ne" : "ano" }}</div>
					@if (!empty($user->getFirstAttribute('employeenumber;x-'.$account['tag']))) 
                                <div class="col-3">{{ empty($account['phones']) ? "" : $account['phones'][0] }}</div>
                                <div class="col-3">{{ empty($account['emails']) ? "" : $account['emails'][0] }}</div>
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
