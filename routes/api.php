<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserVoteLinkController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Api\ClanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionHistoryController;
use App\Http\Controllers\Api\GoogleAuthController;

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
   // lịch sử giao dịch
    Route::get('/transaction-history', [TransactionHistoryController::class, 'getHistory']);
    // user nạp tiềntiền
    Route::post('/add-points', [PaymentController::class, 'addPoints']);
    //for users
    Route::post('users/store', [UserController::class, 'storeUser']);
    Route::post('users', [UserController::class, 'updateUser']);
    // api để người dùng vote cho link
    Route::post('users/vote/{linkId}', [UserVoteLinkController::class, 'vote']);
    // api để lấy lịch sử vote của người dùng
    Route::get('users/vote-history', [UserVoteLinkController::class, 'voteHistory']);
    
    Route::get('clans/top-voter', [ClanController::class, 'getClansWithTopVoter']);

    Route::get('links/search', [UserVoteLinkController::class, 'searchLinks']);

    //logout
    Route::post('/logout', [UserController::class, 'logout']);
});

Route::post('/messages', [MessageController::class, 'saveMessage']);
// api để lấy danh sách rank links
Route::get('links/rank', [UserVoteLinkController::class, 'rankLinks']);


Route::post('/auth/google', [GoogleAuthController::class, 'authenticate']);