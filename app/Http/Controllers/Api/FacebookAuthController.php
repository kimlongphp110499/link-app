<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginWithFacebookRequest;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FacebookAuthController extends Controller
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * login with facebook
     *
     * @param LoginWithFacebookRequest $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function loginWithFacebook(LoginWithFacebookRequest $request): JsonResponse
    {
        $accessToken = $request->input('access_token');

        try {
            $response = $this->client->get("https://graph.facebook.com/v20.0/me?fields=id,name,email,picture&access_token={$accessToken}");
            $payload = json_decode($response->getBody()->getContents(), true);

            if ($payload) {
                $facebookId = $payload['id'];
                $email = $payload['email'] ?? null;
                $name = $payload['name'] ?? null;
                $avatar = $payload['picture']['data']['url'] ?? null;
                if (!$email || !$name) {
                    return response()->json(['message' => 'Missing required fields (email or name)'], 400);
                }

                $existingUser = User::where('email', $email)
                    ->where('facebook_id', '!=', $facebookId)
                    ->first();
                if ($existingUser) {
                    return response()->json(['message' => 'Email already linked to another account'], 400);
                }

                $user = User::where('facebook_id', $facebookId)->first();
                if ($user) {
                    $token = $this->createToken($user);
                    return response()->json([
                        'access_token' => $token,
                        'user' => $user,
                    ], 200);
                }

                $user = User::create([
                    'facebook_id' => $facebookId,
                    'name' => $name,
                    'email' => $email,
                    'avatar' => $avatar,
                ]);
                $token = $this->createToken($user);

                return response()->json([
                        'access_token' => $token,
                        'user' => $user,
                    ], 200);
            }

            return response()->json(['message' => 'Invalid access token'], 400);
        } catch (\Exception $e) {
            Log::error('Facebook login error', [
                'access_token' => $accessToken,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error verifying token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createToken($user)
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
