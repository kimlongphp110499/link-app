@extends('adminlte::page')

@section('title', 'Add New Clan')

@section('content_header')
  <h1>Add New Clan</h1>
@stop

@section('content')
  <form action="{{ route('admin.clans.store') }}"
        method="POST">
    @csrf
    <div class="form-group">
      <label for="name">Clan Name</label>
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

    <button type="submit"
            class="btn btn-primary">Save Clan</button>
  </form>
@stop
