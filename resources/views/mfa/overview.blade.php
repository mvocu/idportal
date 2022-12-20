@extends('layouts.app')

@section('content')

<pre>{{ var_dump(Auth::user()) }}</pre>

@endsection
