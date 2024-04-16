@extends('emailsource::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('emailsource.name') !!}</p>
@endsection
