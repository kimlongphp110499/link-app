<?php

namespace App\Services;

use Google\Client;
use Google\Service\OAuth2;

class GoogleAuthService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
    }

    /**
     * Kiểm tra xem google_id có hợp lệ không
     *
     * @param string $googleId
     * @return bool
     */
    public function isGoogleIdValid($googleId)
    {
        try {
            $oauth2Service = new OAuth2($this->client);

            // Lấy thông tin người dùng từ Google bằng google_id
            $token = $this->client->getAccessToken();
            $this->client->setAccessToken($token);
            $userInfo = $oauth2Service->userinfo->get();

            // Kiểm tra xem google_id có hợp lệ không
            if ($userInfo && $userInfo->id == $googleId) {
                return true; // google_id hợp lệ
            }

            return false; // google_id không hợp lệ
        } catch (\Exception $e) {
            // Nếu có lỗi, google_id không hợp lệ
            return false;
        }
    }
}
