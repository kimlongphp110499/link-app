@extends('adminlte::page')

@section('title', 'Manage Honor')

@section('content_header')
  <h1>Manage Honor</h1>
@stop

@section('content')
  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif
  <a href="{{ route('admin.honors.create') }}"
     class="btn btn-success mb-3">Add New Honor</a>
  <table class="table">
    <thead>
      <tr>
        <th>No.</th>
        <th>Name</th>
        <th>Url</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @if($honors && $honors->count() > 0)
          @foreach ($honors as $key => $honor)
            <tr>
              <td>{{ $key + 1 }}</td>
              <td>{{ $honor->url_name }}</td>
              <td>{{ Str::limit($honor->url, 80, '...') }}</td>
              <td>{{ $honor->date }}</td>
              <td>
                <a href="{{ route('admin.honors.edit', $honor->id) }}"class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('admin.honors.destroy', $honor->id) }}" method="POST" class="delete-form" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm delete-btn">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
      @else
        <tr>
            <td colspan="5">Data does not exist.</td>
        </tr>
      @endif
    </tbody>
  </table>
  {{ $honors->links() }}
@stop

@section('js')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    $(document).ready(function() {
      $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('.delete-btn');
        btn.prop('disabled', true).text('Deleting...');
        Swal.fire({
          title: 'Are you sure?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            form[0].submit();
          } else {
            btn.prop('disabled', false).text('Delete');
          }
        });
      });
    });
  </script>
@endsection
