<?php

use App\Http\Controllers\Api\FacebookAuthController;
use App\Http\Controllers\Api\VideoPlaybackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserVoteLinkController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Api\ClanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionHistoryController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\HonorController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\Api\AppleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transaction-history', [TransactionHistoryController::class, 'getHistory']);
    Route::post('/add-points', [PaymentController::class, 'addPoints']);
    Route::post('users/store', [UserController::class, 'storeUser']);
    Route::post('users', [UserController::class, 'updateUser']);
    Route::post('users/vote/{linkId}', [UserVoteLinkController::class, 'vote']);
    Route::get('users/vote-history', [UserVoteLinkController::class, 'voteHistory']);
    Route::get('clans/top-voter', [ClanController::class, 'getClansWithTopVoter']);
    Route::get('links/search', [UserVoteLinkController::class, 'searchLinks']);
//    Route::get('verify-video/{id}', [VideoPlaybackController::class, 'store']);
    Route::get('user-infor', [UserController::class, 'getUserInfo']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::delete('user/destroy', [UserController::class, 'destroy']);
});

Route::get('video-honors', [HonorController::class, 'index']);

Route::post('/messages', [MessageController::class, 'saveMessage']);
// api để lấy danh sách rank links
Route::get('links/rank', [UserVoteLinkController::class, 'rankLinks']);


Route::post('/auth/google', [GoogleAuthController::class, 'authenticate']);
Route::post('/auth/facebook', [FacebookAuthController::class, 'loginWithFacebook']);
Route::post('/auth/apple', [AppleAuthController::class, 'appleLogin']);
//for api
Route::get('/current-video', [VideoController::class, 'getCurrentVideo']);
