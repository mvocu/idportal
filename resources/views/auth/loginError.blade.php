@extends('layouts.app')

@section('content')

{{ Auth::guard()->getLastError() }}

@endsection
