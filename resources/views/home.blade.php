@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row justify-content-center mt-5">
        <div class="col-md-10">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ __('Google Workspace Account') }}</div>

                <div class="card-body">
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('Name') }}</div>
						<div class="col-7">{{ $oidcuser['name'] }}</div>
					</div>
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('E-mail') }}</div>
						<div class="col-7">{{ $oidcuser['email'] }}</div>
					</div>
                </div>

            </div>
        </div>

		<div class="col-md-6">
			<div class="card">
				<div class="card-header">{{ __('AD Account information') }}</div>
				<div class="card-body">
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('Username') }}</div>
						<div class="col-7">{{ $aduser->getFirstAttribute('samaccountname') }}</div>
					</div>
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('Last logon') }}</div>
						<div class="col-7">{{ $aduser->getFirstAttribute('lastlogon') }}</div>
					</div>
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('Last password change') }}</div>
						<div class="col-7">{{ $aduser->getFirstAttribute('pwdlastset') }}</div>
					</div>
					<div class="row mt-1">
						<div class="col-4 bg-light">{{ __('Account expires') }}</div>
						<div class="col-7">{{ $aduser->getFirstAttribute('accountexpires') }}</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    
</div>
@endsection
