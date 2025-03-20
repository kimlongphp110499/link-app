@extends('adminlte::page')

@section('title', 'Manage Users')

@section('content_header')
  <h1>Manage Users</h1>
@stop

@section('content')
  <a href="{{ route('admin.users.create') }}"
     class="btn btn-success mb-3">Add New User</a>
  <table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Points</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->points }}</td>
          <td>
            <a href="{{ route('admin.users.edit', $user->id) }}"
               class="btn btn-warning">Edit</a>
            <form action="{{ route('admin.users.destroy', $user->id) }}"
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
