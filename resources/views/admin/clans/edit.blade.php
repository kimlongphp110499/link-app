@extends('adminlte::page')

@section('title', 'Edit Clan')

@section('content_header')
  <h1>Edit Clan</h1>
@stop

@section('content')
  <form action="{{ route('admin.clans.update', $clan->id) }}"
        method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
      <label for="name">Clan Name</label>
      <input id="name"
             type="text"
             class="form-control"
             name="name"
             value="{{ old('name', $clan->name) }}"
             required>
      @error('name')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
      <label for="points">Points</label>
      <input id="points"
             type="number"
             class="form-control"
             name="points"
             value="{{ $clan->points }}"
             readonly>
      @error('points')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit"
            class="btn btn-warning">Update Clan</button>
  </form>
@stop
