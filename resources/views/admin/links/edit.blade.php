@extends('adminlte::page')

@section('title', 'Edit Link')

@section('content_header')
  <h1>Edit Link</h1>
@stop

@section('content')
  <form action="{{ route('admin.links.update', $link->id) }}"
        method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
      <label for="title">Title</label>
      <input id="title"
             type="text"
             class="form-control"
             name="title"
             value="{{ old('title', $link->title) }}"
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
             value="{{ old('url', $link->url) }}"
             required>
      @error('url')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit"
            class="btn btn-warning">Update Link</button>
  </form>
@stop
