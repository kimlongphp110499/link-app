@extends('adminlte::page')

@section('title', 'Manage Links')

@section('content_header')
  <h1>Manage Links</h1>
@stop

@section('content')
  <a href="{{ route('admin.links.create') }}"
     class="btn btn-success mb-3">Add New Link</a>
  <table class="table">
    <thead>
      <tr>
        <th>Title</th>
        <th>URL</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($links as $link)
        <tr>
          <td>{{ $link->title }}</td>
          <td>{{ $link->url }}</td>
          <td>
            <a href="{{ route('admin.links.edit', $link->id) }}"
               class="btn btn-warning">Edit</a>
            <form action="{{ route('admin.links.destroy', $link->id) }}"
                  method="POST"
                  style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit"
                      class="btn btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@stop
