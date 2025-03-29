<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:15',
            // 'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Cập nhật thông tin người dùng
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'password' => $request->password ? Hash::make($request->password) : $user->password, // Nếu không có mật khẩu mới, giữ nguyên mật khẩu cũ
        ]);

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user
        ], 200);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:15',
            'google_id' => 'required|string|max:191',
            'avatar' => 'nullable|string|max:191',
        ]);

        $user = User::create([
            'google_id' => $request->google_id,
            'avatar' => $request->avatar,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user
        ], 200);
    }
}
