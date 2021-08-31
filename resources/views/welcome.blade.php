@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card" style="margin-top: 10rem">
				<div class="card-header">Úvodní informace</div>
	        	<div class="card-body">
	        		<h4 class="card-title">Nastavení hesla pro AD doménu ZŠ Třebotov</h4>
					<p class="card-text">
						Tato aplikace slouží pro prvotní nastavení hesla na počítačích v doméně ZŠ Třebotov.
					</p> 
					<p class="card-text">
						Pro ověření vaší totožnosti vás nyní přesměrujeme na přihlášení pomocí vašeho školního účtu v Google Workspace. 
					</p> 
    	        </div>
    	        <div class="card-footer text-right">
					<a class="btn btn-primary" href="{{ route('adpassword') }}">{{ __('Continue'); }}</a>
    	        </div>
			</div>
        </div>
    </div>
</div>
@endsection
