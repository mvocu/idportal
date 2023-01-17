@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<h4>{{ __('Voting codes') }}</h4>
			<hr style="border-top-color: #7bbb57; margin-top: 0px" />
			<p>{{ __('Voting in participative budget is available only to permanent residents. Before we assign you'
			. ' a new voting code, we need you to sign the following declaration:') }}
			</p>
		</div>
	</div>
    <div class="row justify-content-center">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Declaration of permanent residence') }}</div>

                <div class="panel-body">
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
              	
                    <form method="POST" action="{{ route('voting.declare') }}" aria-label="{{ __('Declaration form') }}">
                        {{ csrf_field() }}

						<div class="form-group row">
							<div class="col-xs-12">
								{{ __('I declare that I have permanent residency here.') }} 
							</div>
						</div>

                        <div class="form-group row">
                            <div class="col-md-4 col-md-offset-7 text-right">
                                <input id="consent_check" type="checkbox" class="" name="consent_check" value="agree" required>
	                            <label for="consent_check" class="control-label col-form-label">{{ __('I confirm the above declaration') }}</label>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-4 col-md-offset-7 text-right">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }} 
                                </button>
                            </div>
                        </div>
                    </form>

              	</div>

            	<div class="panel-footer">
					<div class="row">
						<div class="col-xs-2"><a href="{{ route('home') }}" class="btn btn-default btn-block"><span class="fa fa-long-arrow-left">&nbsp;</span>{{ __('Back') }}</a></div>
					</div>
            	</div>

            </div>
        </div>
    </div>
</div>
@endsection
                    