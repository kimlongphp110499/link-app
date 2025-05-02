@extends('adminlte::page')

@section('title', 'Add New Link')

@section('content_header')
  <h1>Add New Link</h1>
@stop
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-results__option {
        background-color: #f0f8ff;
        color: #333;
    }
    .select2-container--default .select2-results__option--highlighted {
        background-color: #1e90ff;
        color: #fff;
    }
    .select2-container--default .select2-selection__choice {
        background-color: #32cd32;
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
</style>
@endpush
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
      <label for="clan">Assign Clan</label>
      <select name="clan_ids[]" id="clan_ids" multiple  class="form-control">
            @foreach ($clans as $clan)
                <option value="{{ $clan->id }}">
                    {{ $clan->name }}
                </option>
            @endforeach
        </select>
      @error('clan_id')
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
     $(document).ready(function() {
        $('#clan_ids').select2();
    });
    $('#duration').on('input', function() {
        const seconds = parseInt($(this).val()) || 0;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const formattedTime = `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
        $('#duration-display').text(formattedTime);
    });
</script>
@stop