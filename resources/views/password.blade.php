@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-10">
			<div class="card">
				<div class="card-header">{{ __('Set New Password') }}</div>
				<div class="card-body">

					<div class="row no-gutters">
						<div class="col-md-7">
							<h4 class="card-title text-center mb-4">{{ __('Please enter new password') }}</h4>

							<form method="POST" action="{{ route('adpassword.set') }}">
								@csrf

								<div class="form-group row">
									<label for="password"
										class="col-md-4 col-form-label text-md-right">{{
										__('Password') }}</label>

									<div class="col-md-7">
										<input id="password" type="password"
											class="form-control @error('password') is-invalid @enderror"
											name="password" required autocomplete="current-password">

										@error('password') <span class="invalid-feedback" role="alert">
											<strong>{{ $message }}</strong>
										</span> @enderror
									</div>
								</div>

								<div class="form-group row">
									<label for="password-confirm"
										class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

									<div class="col-md-7">
										<input id="password-confirm" type="password"
											class="form-control" name="password_confirmation" required
											autocomplete="new-password">
									</div>
								</div>

								<div class="form-group row mb-0">
									<div class="col-md-8 offset-md-4">
										<button type="submit" class="btn btn-primary">{{ __('Change Password') }}</button>
									</div>
								</div>
							</form>
						</div>
						<div class="col-md-5 bg-light">
							<p class="card-text p-3">Zadejte heslo, které budete používat pro
								přihlášení k počítačům na ZŠ Třebotov.</p>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection