@extends('adminlte::page')
@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
<style>
    /* Button styles */
    .control-button {
        padding: 15px 30px;
        font-size: 18px;
        font-weight: bold;
        color: white;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        outline: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    /* Start state */
    .start {
        background: linear-gradient(45deg, #28a745, #34c759);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    }

    .start:hover {
        background: linear-gradient(45deg, #34c759, #28a745);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.5);
    }

    /* Stop state */
    .stop {
        background: linear-gradient(45deg, #dc3545, #ff4d4d);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
    }

    .stop:hover {
        background: linear-gradient(45deg, #ff4d4d, #dc3545);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.5);
    }

    /* Icon animation */
    .control-button i {
        transition: transform 0.3s ease;
    }

    .control-button:hover i {
        transform: scale(1.2);
    }
</style>
@endpush
@section('title', 'Manage Links')

@section('content_header')
  <h1>Manage Links</h1>
@stop

@section('content')
  <a href="{{ route('admin.links.create') }}"
     class="control-button start mb-3">Add New Link</a>
    <button id="control-button" class="control-button @if($checkSchedule > 0) stop @else start @endif mb-3">
      <i class="@if($checkSchedule > 0) fas fa-pause @else fas fa-play @endif"></i> @if($checkSchedule > 0) Stop Live @else Start Live @endif
    </button>

    <p id="status"></p>
    <form action="{{ route('admin.links.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">Upload:</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
  <table class="table-striped table-bordered table">
    <thead>
      <tr>
        <th>Title</th>
        <th>URL</th>
        <th>Video ID</th>
        <th>Points</th>
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
          <td>{{ $link->total_votes }}</td>
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
@section('js')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        let isPlaying = @json(!empty($checkSchedule) && $checkSchedule > 0);
        const controlButton = document.getElementById('control-button');
        const status = document.getElementById('status');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        controlButton.addEventListener('click', () => {
            const newValue = !isPlaying; // true nếu Start, false nếu Stop

            // Gửi request để sửa file .env
            fetch('/admin/video-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    key: 'VIDEO_PLAYING',
                    value: newValue.toString(),
                }),
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                status.textContent = data.message;

                // Cập nhật giao diện button
                if (isPlaying) {
                    // Chuyển sang trạng thái "Start"
                    controlButton.classList.remove('stop');
                    controlButton.classList.add('start');
                    controlButton.innerHTML = '<i class="fas fa-play"></i> Start Live';
                    isPlaying = false;
                } else {
                    // Chuyển sang trạng thái "Stop"
                    controlButton.classList.remove('start');
                    controlButton.classList.add('stop');
                    controlButton.innerHTML = '<i class="fas fa-pause"></i> Stop Live';
                    isPlaying = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                status.textContent = 'Error updating video state';
            });
        });
    </script>
</body>
</html>
    @stop