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
    <div class="form-group">
      <label for="video_id">Video ID</label>
      <input id="video_id"
             type="text"
             class="form-control"
             name="video_id"
             value="{{ old('video_id') }}"
             required>
      @error('video_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <div class="form-group">
        <label for="duration">Duration (seconds)</label>
        <div class="input-group">
            <input type="number" name="duration" class="form-control" id="duration"  value="{{ old('duration') }}" placeholder="Enter duration in seconds" min="1" required>
            <div class="input-group-append">
                <span class="input-group-text" id="duration-display">0:00</span>
            </div>
        </div>
        @error('duration')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit"
            class="btn btn-primary">Save Link</button>
  </form>
@stop
@section('js')
<script>
    $('#duration').on('input', function() {
        const seconds = parseInt($(this).val()) || 0;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const formattedTime = `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
        $('#duration-display').text(formattedTime);
    });
</script>
@stop