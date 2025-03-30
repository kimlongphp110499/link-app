<?php

namespace App\Http\Controllers\Api;

use App\Services\GoogleAuthService;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;

class GoogleAuthController extends Controller
{
    // protected $googleAuthService;

    // public function __construct(GoogleAuthService $googleAuthService)
    // {
    //     $this->googleAuthService = $googleAuthService;
    // }

    // // Kiểm tra google_id
    // public function checkGoogleId(Request $request)
    // {
    //     $request->validate([
    //         'google_id' => 'required|string|max:191',
    //     ]);

    //     $googleId = $request->google_id;

    //     // Kiểm tra xem google_id có hợp lệ không
    //     if ($this->googleAuthService->isGoogleIdValid($googleId)) {
    //         return response()->json([
    //             'message' => 'Google ID is valid!',
    //         ], 200);
    //     }

    //     return response()->json([
    //         'message' => 'Invalid Google ID!',
    //     ], 400);
    // }

    // for test 
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Xử lý callback từ Google
    public function handleGoogleCallback()
    {
        // Lấy thông tin người dùng từ Google
        $googleUser = Socialite::driver('google')->user();

        // Lấy Google ID
        $googleId = $googleUser->getId();  // Đây là Google ID bạn cần
dd($googleUser);
        // Lưu Google ID vào cơ sở dữ liệu hoặc thực hiện các hành động khác
        $user = User::updateOrCreate(
            ['google_id' => $googleId],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
            ]
        );

        // Đăng nhập người dùng
        Auth::login($user);

        // Trả về thông tin người dùng đã đăng nhập (Google ID)
        return response()->json([
            'message' => 'Google ID is valid!',
            'google_id' => $googleId,
            'user' => $user
        ]);
    }
}

