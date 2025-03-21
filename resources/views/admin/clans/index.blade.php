@extends('adminlte::page')

@section('title', 'Manage Clans')

@section('content_header')
  <h1>Manage Clans</h1>
@stop

@section('content')
  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  <a href="{{ route('admin.clans.create') }}"
     class="btn btn-success mb-3">Add New Clan</a>
  <table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Points</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($clans as $clan)
        <tr>
          <td>{{ $clan->name }}</td>
          <td>{{ $clan->points }}</td>
          <td>
            <a href="{{ route('admin.clans.edit', $clan->id) }}"
               class="btn btn-warning">Edit</a>
            <form action="{{ route('admin.clans.destroy', $clan->id) }}"
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
  {{ $clans->links() }}
@stop
