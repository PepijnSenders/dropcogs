@extends('layouts.default')

@section('content')
{{ $user->display_name }}

@foreach (Pep\Dropcogs\Dropbox\Dropbox::getMetadataWithChildren('/Muziek')['contents'] as $folder)
<p>{{ $folder['path'] }}</p>
@endforeach
@stop