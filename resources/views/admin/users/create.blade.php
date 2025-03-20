@extends('adminlte::page')

@section('title', 'Add New User')

@section('content_header')
  <h1>Add New User</h1>
@stop

@section('content')
  <form action="{{ route('admin.users.store') }}"
        method="POST">
    @csrf
    <div class="form-group">
      <label for="name">Name</label>
      <input id="name"
             type="text"
             class="form-control"
             name="name"
             value="{{ old('name') }}"
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
             value="{{ old('email') }}"
             required>
      @error('email')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input id="password"
             type="password"
             class="form-control"
             name="password"
             required>
      @error('password')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="password_confirmation">Confirm Password</label>
      <input id="password_confirmation"
             type="password"
             class="form-control"
             name="password_confirmation"
             required>
    </div>
    <button type="submit"
            class="btn btn-primary">Save User</button>
  </form>
@stop
