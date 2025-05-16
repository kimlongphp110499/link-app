<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Hiển thị danh sách users
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    // Hiển thị form tạo user
    public function create()
    {
        session()->put('return_url', url()->previous());
        return view('admin.users.create');
    }

    // Lưu thông tin user mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // 'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            // 'password' => Hash::make($request->password),
        ]);

        return redirect()->to(session('return_url', route('admin.users.index')))
        ->with('success', 'User created successfully.');
    }

    // Hiển thị form chỉnh sửa user
    public function edit($id)
    {
        session()->put('return_url', url()->previous());
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    // Cập nhật thông tin user
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'points' => 'required|integer|min:0',
            // 'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $oldPoint = $user->points;
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'points' => $request->points,
            // 'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);
        if($request->points != $oldPoint)
        {
            $payload = json_encode([
                'user_id' => $id,
                'points' => $request->points,
            ]);
        
            Log::info('Attempting to publish to Redis', ['channel' => 'points-update', 'payload' => $payload]);
        
            try {
                Redis::publish('points-update', $payload);
                Log::info('Successfully published to Redis', ['channel' => 'points-update', 'payload' => $payload]);
            } catch (\Exception $e) {
                Log::error('Failed to publish to Redis', [
                    'error' => $e->getMessage(),
                    'channel' => 'points-update',
                    'payload' => $payload
                ]);
                return response()->json(['message' => 'Failed to publish to Redis', 'error' => $e->getMessage()], 500);
            }
        }

        return redirect()->to(session('return_url', route('admin.users.index')))
        ->with('success', 'User updated successfully.');
    }

    // Xóa user
    public function destroy($id)
    {
        session()->put('return_url', url()->previous());
        User::findOrFail($id)->delete();
                return redirect()->to(session('return_url', route('admin.users.index')))
        ->with('success', 'User deleted successfully.');
    }

    
}