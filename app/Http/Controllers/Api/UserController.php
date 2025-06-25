<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function updateUser(Request $request)
    {
        $auth =  auth()->user();
        $user = User::findOrFail($auth->id);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15|unique:users,phone,' . $auth->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:200',
            'nick_name' => 'nullable|string|max:255|unique:users,nick_name,' . $auth->id,
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
            'phone' => 'nullable|string|max:15|unique:users',
            'avatar' => 'nullable|string|max:191',
            'nick_name' => 'nullable|string|max:191',
            'google_id' => 'required|string|unique:users',
        ]);

        $user = User::create([
            'avatar' => $request->avatar,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'nick_name' => $request->nick_name,
            'google_id' => $request->google_id,
        ]);

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user
        ], 200);
    }

    public function getUserInfo(Request $request)
    {
        $user =  auth()->user();
         
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
 
        return response()->json([
            'status' => 'success',
            'user' => [
                $user
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function destroy(Request $request)
    {
        // Xóa user đang đăng nhập
        $user = $request->user(); // Lấy thông tin user đang đăng nhập

        if ($user) {
            $user->tokens()->delete(); // Xóa tất cả token đăng nhập của user (không bắt buộc)
            $user->delete(); // Xóa user khỏi database

            return response()->json([
                'message' => 'User has been deleted successfully.',
            ], 200);
        }

        return response()->json([
            'message' => 'User not found or not authenticated.',
        ], 404);
    }
}
