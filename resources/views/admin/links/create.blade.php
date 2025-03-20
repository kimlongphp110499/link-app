@extends('adminlte::page')

@section('title', 'Add New Link')

@section('content_header')
  <h1>Add New Link</h1>
@stop

@section('content')
  <form action="{{ route('admin.links.store') }}"
        method="POST">
    @csrf
    <div class="form-group">
      <label for="title">Title</label>
      <input id="title"
             type="text"
             class="form-control"
             name="title"
             value="{{ old('title') }}"
             required>
      @error('title')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="url">URL</label>
      <input id="url"
             type="url"
             class="form-control"
             name="url"
             value="{{ old('url') }}"
             required>
      @error('url')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit"
            class="btn btn-primary">Save Link</button>
  </form>
@stop
