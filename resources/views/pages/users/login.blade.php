@extends('layouts.default')

@section('content')
@if (isset($message))
<p class="text-danger">{{ $message }}</p>
@endif
<a href="{{ $url }}">Login</a>
@stop