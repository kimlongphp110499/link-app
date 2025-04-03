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
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nick_name' => 'nullable|string|max:255',
            // 'password' => 'nullable|string|min:6|confirmed',
        ]);
        // Cập nhật thông tin người dùng
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'nick_name' => $request->nick_name,
            // 'password' => $request->password ? Hash::make($request->password) : $user->password, // Nếu không có mật khẩu mới, giữ nguyên mật khẩu cũ
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                // Xóa ảnh cũ từ thư mục
                $oldAvatarPath = public_path($user->avatar);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            $image = $request->file('avatar');
    
            // Tạo tên ảnh ngẫu nhiên để tránh trùng lặp
            $imageName = time() . '.' . $image->getClientOriginalExtension();
        
            // Lưu ảnh vào thư mục 'storage/app/public/users/avatar'
            $image->storeAs('public/users', $imageName);
        
            // Cập nhật đường dẫn ảnh trong cơ sở dữ liệu
            $user->update([
                'avatar' => 'storage/users/' . $imageName, // Lưu đường dẫn tương đối từ thư mục public
            ]);
        }

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
            'avatar' => 'nullable|string|max:191',
            'nick_name' => 'nullable|string|max:191',
        ]);

        $user = User::create([
            'avatar' => $request->avatar,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'nick_name' => $request->nick_name,
        ]);

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user
        ], 200);
    }
}
