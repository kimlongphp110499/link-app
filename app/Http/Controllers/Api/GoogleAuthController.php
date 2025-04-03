<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Google_Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        $idToken = $request->input('id_token');

        try {
            $googleUser = Socialite::driver('google')->userFromToken($idToken);
            return response()->json([
                'id' => $googleUser->id,
                'email' => $googleUser->email,
                'name' => $googleUser->name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    
        // $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

        // try {
        //     $payload = $client->verifyIdToken($idToken);

        //     if ($payload) {
        //         $googleId = $payload['sub'];
        //         $email = $payload['email'];
        //         $name = $payload['name'];

        //         $user = User::firstOrCreate(
        //             ['google_id' => $googleId],
        //             ['name' => $name, 'email' => $email]
        //         );

        //         Auth::login($user);

        //         return response()->json([
        //             'message' => 'User authenticated successfully',
        //             'user' => $user
        //         ]);
        //     }

        //     return response()->json(['message' => 'Invalid ID token'], 400);

        // } catch (\Exception $e) {
        //     dd($e);
        //     return response()->json(['message' => 'Error verifying token'], 500);
        // }
    }

    // public function authenticate_c(Request $request)
    // {
    //       // Lấy Access Token từ request
    //       $accessToken = $request->input('access_token');

    //       // Gửi yêu cầu tới Google People API để lấy thông tin người dùng
    //       $url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $accessToken;
  
    //       // Gửi GET request để lấy thông tin người dùng
    //       $response = file_get_contents($url);
  
    //       // Chuyển đổi dữ liệu JSON thành mảng PHP
    //       $userInfo = json_decode($response, true);
  
    //       // Kiểm tra nếu có lỗi từ Google API
    //       if (isset($userInfo['error'])) {
    //           return response()->json(['message' => 'Error retrieving user info', 'error' => $userInfo['error']], 400);
    //       }
  
    //       // Nếu không có lỗi, trả về thông tin người dùng
    //       return response()->json([
    //           'message' => 'User authenticated successfully',
    //           'user' => $userInfo
    //       ]);
    // }
}