@extends('adminlte::page')

@section('title', 'Admin List')

@section('content_header')
    <h1>Admin List</h1>
@stop

@section('content')
    <a href="{{ route('admin.create') }}" class="btn btn-success">Add Admin</a>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($admins as $admin)
                <tr>
                    <td>{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        <a href="{{ route('admin.edit', $admin) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.destroy', $admin) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
