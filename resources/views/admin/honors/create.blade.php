@extends('adminlte::page')

@section('title', 'Add New Honor')

@section('content_header')
    <h1>Add New Honor</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('content')
    <form action="{{ route('admin.honors.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="url_name">Url Name</label>
            <input id="url_name" type="text" class="form-control" name="url_name" value="{{ old('url_name') }}" required>
            @error('url_name')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <label for="url">Url</label>
            <input id="url" type="url" class="form-control" name="url" value="{{ old('url') }}" required>
            @error('url')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <label for="date">Date</label>
            <input id="date" type="text" class="form-control" name="date" value="{{ old('date') }}" required>
            @error('date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save Honor</button>
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
