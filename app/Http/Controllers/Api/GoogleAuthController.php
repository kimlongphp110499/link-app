<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Google_Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

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
    
        $platform = $request->header('X-Platform');

        if (strtolower($platform) === 'ios') {
            $client = new Google_Client(['client_id' => env('GOOGLE_IOS_CLIENT_ID')]);
        } else {
            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        }

        try {
            $payload = $client->verifyIdToken($idToken);

            if ($payload) {
                $googleId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'];
                $avatar = $payload['picture'];

                $user = User::where('google_id', $googleId)->first();

                if ($user) {
                    $token = $user->createToken('auth_token')->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'user' => $user,
                    ], 200);
                } else {
                    $user = User::create([
                        'google_id' => $googleId,
                        'name' => $name,
                        'email' => $email,
                        'avatar' => $avatar,
                    ]);
        
                    $token = $user->createToken('auth_token')->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'user' => $user,
                    ], 200);
                }
            }

            return response()->json(['message' => 'Invalid ID token'], 400);

        } catch (\Exception $e) {

            return response()->json(['message' => 'Error verifying token'], 500);
        }
    }
}