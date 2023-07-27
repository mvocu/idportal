@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{ __('Account status') }}</div>

                <div class="panel-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->has('failure'))
                        <div class="alert alert-danger" role="alert">
                            {!! __($errors->first('failure')) !!}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('account.search') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label for="uid" class="col-md-4 control-label">{{ __('Login, email or phone number') }}</label>

                            <div class="col-md-6">
                                <input id="uid" type="text" class="form-control" name="uid" value="{{ old('uid') }}" required autofocus>

                                @if ($errors->has('uid'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('uid') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection
