@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
  <h1>Edit User</h1>
@stop

@section('content')
  <form action="{{ route('admin.users.update', $user->id) }}"
        method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
      <label for="name">Name</label>
      <input id="name"
             type="text"
             class="form-control"
             name="name"
             value="{{ old('name', $user->name) }}"
             required>
      @error('name')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input id="email"
             type="email"
             class="form-control"
             name="email"
             value="{{ old('email', $user->email) }}"
             required>
      @error('email')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="points">Points</label>
      <input id="points"
             type="number"
             class="form-control"
             name="points"
             value="{{ old('points', $user->points) }}"
             >
      @error('points')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="password">Password (Leave blank to keep unchanged)</label>
      <input id="password"
             type="password"
             class="form-control"
             name="password">
    </div>
    <div class="form-group">
      <label for="password_confirmation">Confirm Password</label>
      <input id="password_confirmation"
             type="password"
             class="form-control"
             name="password_confirmation">
    </div>
    <button type="submit"
            class="btn btn-warning">Update User</button>
  </form>
@stop
