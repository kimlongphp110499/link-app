<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AppleAuthController extends Controller
{
    /**
     * login with facebook
     *
     * @param LoginWithFacebookRequest $request
     * @return JsonResponse
     * @throws GuzzleException
     */

    public function appleLogin(Request $request)
    {
        // Xác thực input
        $request->validate([
            'identityToken' => 'required|string',
            'name' => 'nullable|string'
        ]);

        $idToken = $request->input('identityToken');
        $userName = $request->input('name');

        try {
            // Lấy khóa công khai từ Apple
            $response = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
            if (!$response->successful()) {
                Log::error('Failed to fetch Apple public keys', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['message' => 'Unable to fetch Apple public keys'], 500);
            }

            $keys = $response->json();
            if (!isset($keys['keys']) || !is_array($keys['keys'])) {
                Log::error('Invalid JWK Set from Apple', ['response' => $keys]);
                return response()->json(['message' => 'Invalid JWK Set: "keys" member missing'], 500);
            }

            $publicKeys = JWK::parseKeySet($keys);

            $decoded = JWT::decode($idToken, $publicKeys);

            // Kiểm tra issuer và audience
            if ($decoded->iss !== 'https://appleid.apple.com' || $decoded->aud !== env('APPLE_CLIENT_ID')) {
                Log::warning('Invalid issuer or audience', [
                    'iss' => $decoded->iss,
                    'aud' => $decoded->aud,
                    'expected_aud' => env('APPLE_CLIENT_ID')
                ]);

                return response()->json(['message' => 'Invalid issuer or audience'], 400);
            }

            // Lấy thông tin người dùng
            $appleId = $decoded->sub;
            $email = $decoded->email ?? null;
            $name = isset($userName)
                ? ($userName)
                : 'Apple User';

            // Tìm hoặc tạo người dùng
            $user = User::where('apple_id', $appleId)->first();

            if (!$user) {
                 $user = User::create([
                    'apple_id' => $appleId,
                    'name' => $name,
                    'email' => $email,
                    'avatar' => null,
                ]);
            }

            // Tạo token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'apple_id' => $user->apple_id,
                ],
            ], 200);

        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::error('Apple token expired', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Token expired'], 400);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('Invalid Apple token signature', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid token signature'], 400);
        } catch (\Exception $e) {
            Log::error('Apple login error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error verifying token: ' . $e->getMessage()], 500);
        }
    }
}
