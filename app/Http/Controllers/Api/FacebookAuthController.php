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
        $platform = $request->header('X-Platform');
        $client = new Client();
        if (strtolower($platform) === 'ios') {
            $fbClientId = env('FACEBOOK_APP_ID_IOS');
            $fbClientToken = env('FACEBOOK_CLIENT_TOKEN_IOS');
        } else {
            $fbClientId = env('FACEBOOK_APP_ID_ANDROID');
            $fbClientToken = env('FACEBOOK_CLIENT_TOKEN_ANDROID');
        }

        try {
            $response = $client->get('https://graph.facebook.com/debug_token', [
                'query' => [
                    'input_token' => $accessToken,
                    'access_token' => $fbClientId . '|' . $fbClientToken,
                ],
            ]);
            $result = json_decode($response->getBody(), true);
            if (isset($result['data']['is_valid']) && $result['data']['is_valid']) {
                $userResponse = $client->get('https://graph.facebook.com/me', [
                    'query' => [
                        'fields' => 'id,name,email,picture',
                        'access_token' => $accessToken,
                    ],
                ]);
                $fbUser = json_decode($userResponse->getBody(), true);
                $email = $fbUser['email'] ?? null;
                $facebookId = $fbUser['id'];
                $name = $fbUser['name'] ?? null;
                $avatar = $fbUser['picture']['data']['url'] ?? null;
                $existingUser = User::where('email', $email)
                    ->where('facebook_id', '!=', $facebookId)
                    ->first();


                if ($existingUser) {
                    return response()->json(['message' => 'Email already linked to another account'], 400);
                }

                $userCheck = User::where('facebook_id', $facebookId)
                            ->first();
                if ($userCheck) {
                    $token = $this->createToken($userCheck);
                    return response()->json([
                        'access_token' => $token,
                        'user' => $userCheck,
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
                'facebook_access_token' => $accessToken,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Error verifying token'], 500);
        }
    }

    public function createToken($user)
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
