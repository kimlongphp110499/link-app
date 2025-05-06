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
use App\Http\Controllers\HonorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\CKFinderController;
use Illuminate\Support\Facades\File;

Route::get('/error-log', function () {
    $date = request('date', now()->format('Y-m-d'));
    $logFilePath = storage_path("logs/error-{$date}.log");

    if (!File::exists($logFilePath)) {
        return view('error-log', [
            'logs' => [],
            'error' => "Log file does not exist."
        ]);
    }

    $logs = File::lines($logFilePath)->toArray();

    $parsedLogs = [];
    foreach ($logs as $line) {
        if (preg_match('/^\[(.*?)\] (.*?): (.*?)$/', $line, $matches)) {
            $parsedLogs[] = [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => $matches[3],
                'details' => ''
            ];
        } elseif (!empty($parsedLogs)) {
            $parsedLogs[array_key_last($parsedLogs)]['details'] .= $line . "\n";
        }
    }

    return view('error-log', [
        'logs' => $parsedLogs,
        'error' => null
    ]);
});

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::name('admin.')->middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [AdminController::class, 'index'])->name('list');
    Route::resource('links', LinkController::class);
    Route::resource('users', UserController::class);
    Route::resource('clans', ClanController::class);
    Route::resource('honors', HonorController::class);

    // Cộng điểm cho clan
    Route::post('clans/{clanId}/add-points', [ClanController::class, 'addPoints']);
    Route::post('links/{linkId}/assign-clan', [LinkController::class, 'assignClan'])->name('links.assign-clan');

    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/video-status', [LinkController::class, 'videoStatus']);
    Route::get('/policy/edit', [PolicyController::class, 'edit'])->name('policy.edit');
    Route::post('/policy', [PolicyController::class, 'update'])->name('policy.update');
    Route::post('/ckfinder/upload', [CKFinderController::class, 'upload'])->name('ckfinder.upload');
    Route::post('/links/import', [LinkController::class, 'import'])->name('links.import');
});
Route::resource('admin', AdminController::class)->middleware('auth:admin');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/policy', [PolicyController::class, 'show'])->name('policy.show');
