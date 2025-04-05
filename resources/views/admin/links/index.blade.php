@extends('adminlte::page')
@push('css')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .clan-tag {
        display: inline-flex;
        align-items: center;
        background-color: #007bff; /* Màu xám nhạt giống ảnh */
        color: #ffff; /* Chữ đen */
        padding: 2px 6px;
        margin: 2px;
        border-radius: 3px;
        font-size: 0.9em;
    }
</style>
@endpush
@section('title', 'Manage Links')

@section('content_header')
  <h1>Manage Links</h1>
@stop

@section('content')
  <a href="{{ route('admin.links.create') }}"
     class="btn btn-success mb-3">Add New Link</a>

  <table class="table-striped table-bordered table">
    <thead>
      <tr>
        <th>Title</th>
        <th>URL</th>
        <th>Video ID</th>
        <th>Assigned Clan</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($links as $link)
        <tr>
          <td>{{ $link->title }}</td>
          <td>
            <!-- Giới hạn độ dài URL hiển thị -->
            <span title="{{ $link->url }}">{{ Str::limit($link->url, 50) }}</span>
          </td>
          <td>{{ $link->video_id }}</td>
          <td>
            @if ($link->clans->isNotEmpty())
                @foreach ($link->clans as $clan)
                    <span class="clan-tag">
                        {{ $clan->name }}
                    </span>
                @endforeach
            @else
                <span class="text-muted">No Clan Assigned</span>
            @endif
          </td>
          <td>
            <a href="{{ route('admin.links.edit', $link->id) }}"
               class="btn btn-warning btn-sm">Edit</a>
            <form action="{{ route('admin.links.destroy', $link->id) }}"
                  method="POST"
                  style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit"
                      class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  {{ $links->links() }}

@stop