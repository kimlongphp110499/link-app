<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClanController;

// use App\Http\Controllers\Api\GoogleAuthController;

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::name('admin.')->middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [AdminController::class, 'index'])->name('list');
    Route::resource('links', LinkController::class);
    Route::resource('users', UserController::class);
    Route::resource('clans', ClanController::class);

    // Cộng điểm cho clan
    Route::post('clans/{clanId}/add-points', [ClanController::class, 'addPoints']);
    Route::post('links/{linkId}/assign-clan', [LinkController::class, 'assignClan'])->name('links.assign-clan');

});
Route::resource('admin', AdminController::class)->middleware('auth:admin');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('login/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
// Route::get('login/google', [GoogleAuthController::class, 'redirectToGoogle']);