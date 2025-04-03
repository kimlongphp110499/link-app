@extends('adminlte::page')
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
    <div class="form-group">
      <label for="clan">Assign Clan</label>
      <select name="clan_ids[]" id="clan_ids" multiple required  class="form-control">
            @foreach ($clans as $clan)
                <option value="{{ $clan->id }}" {{ in_array($clan->id, $selectedClans) ? 'selected' : '' }}>
                    {{ $clan->name }}
                </option>
            @endforeach
        </select>
      @error('clan_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
    <button type="submit"
            class="btn btn-warning">Update Link</button>
  </form>
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
        $('#clan_ids').select2();
    });
</script>
@stop