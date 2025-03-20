<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserVoteLinkController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\MessageController;

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


// api để người dùng vote cho link
Route::post('users/{userId}/vote/{linkId}', [UserVoteLinkController::class, 'vote']);

// api để lấy lịch sử vote của người dùng
Route::get('users/{userId}/vote-history', [UserVoteLinkController::class, 'voteHistory']);

// api để lấy danh sách rank links
Route::get('links/rank', [UserVoteLinkController::class, 'rankLinks']);
// api để lấy danh sách rank links

Route::get('links/search', [UserVoteLinkController::class, 'searchLinks']);
// api để lấy danh sách rank links

Route::put('users/{userId}', [UserController::class, 'updateUser']);

Route::post('/messages', [MessageController::class, 'saveMessage']);