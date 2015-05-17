@extends('layouts.default')

@section('content')
<table class="table table-striped">
  <thead>
    <tr>
      <th>
        <a href="{{ URL::route('pages.users.configure', ['path' => '']) }}">/</a>
        <?php $url = ''; ?>
        @foreach ($basePath as $piece)
          <a href="{{ URL::route('pages.users.configure', ['path' => $url .= "/$piece"]) }}">{{ $piece }}</a>
          /
        @endforeach
      </th>
    </tr>
  </thead>
  <tbody>
    @foreach ($metadata['contents'] as $file)
    @if ($file['is_dir'])
      <tr>
        <td>
          <?php $pieces = explode('/', $file['path']); ?>
          <a href="{{ URL::route('pages.users.configure', ['path' => $file['path']]) }}">{{ $pieces[count($pieces) - 1] }}</a>
        </td>
        <td>
          @if (Pep\Dropcogs\Folder::isIncluded($file['path']))
          <form action="{{ URL::route('folders.remove') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="path" value="{{ $file['path'] }}">
            <button class="btn btn-warning">Remove</button>
          </form>
          @else
          <form action="{{ URL::route('folders.add') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="path" value="{{ $file['path'] }}">
            <button class="btn btn-success">Add</button>
          </form>
          @endif
      </tr>
    @endif
    @endforeach
  </tbody>
</table>
<a href="{{ URL::route('dropbox.analyze') }}" class="btn btn-default btn-lg">Continue</a>
@stop