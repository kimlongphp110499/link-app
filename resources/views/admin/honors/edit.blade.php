@extends('adminlte::page')
@section('title', 'Edit Honor')
@section('content_header')
  <h1>Edit Honor</h1>
@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('content')
  <form action="{{ route('admin.honors.update', $honor->id) }}"
        method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
      <label for="url_name">Url Name</label>
      <input id="url_name" type="text" class="form-control" name="url_name" value="{{ old('url_name', $honor->url_name) }}" required>
      @error('url_name')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <label for="url">Url</label>
      <input id="url" type="text" class="form-control" name="url" value="{{ old('url', $honor->url) }}" required>
      @error('url')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <label for="date">Date</label>
      <input id="date" type="text" class="form-control" name="date" value="{{ old('date', $honor->date) }}" required>
      @error('date')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <div class="form-group">
        <label for="duration">Duration (seconds)</label>
        <div class="input-group">
            <input type="number" name="duration" class="form-control" id="duration" value="{{ $honor->duration }}" min="1" required>
            <div class="input-group-append">
                <span class="input-group-text" id="duration-display">
                    @php
                        $minutes = floor($honor->duration / 60);
                        $seconds = $honor->duration % 60;
                        echo $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;
                    @endphp
                </span>
            </div>
        </div>
    </div>
    </div>
    <button type="submit" class="btn btn-warning">Update Honor</button>
  </form>
@stop

@section('js')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date", {
            enableTime: true,
            dateFormat: "Y/m/d H:i",
            altInput: true,
            altFormat: "d/m/Y H:i",
            time_24hr: true,
        });
    </script>
@stop
