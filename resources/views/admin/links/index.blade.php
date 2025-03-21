@extends('adminlte::page')

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
          <td>
            @if ($link->clan)
              {{ $link->clan->name }}
            @else
              <form action="{{ route('admin.links.assign-clan', $link->id) }}"
                    method="POST"
                    style="display:inline;">
                @csrf
                <div class="input-group">
                  <select name="clan_id"
                          class="form-control">
                    <option value=""></option>
                    @foreach ($clans as $clan)
                      <option value="{{ $clan->id }}">{{ $clan->name }}</option>
                    @endforeach
                  </select>
                  <div class="input-group-append">
                    <button type="submit"
                            class="btn btn-primary">Assign Clan</button>
                  </div>
                </div>
              </form>
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
