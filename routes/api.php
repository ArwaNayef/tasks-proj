<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

Route::post('/manager/register', [\App\Http\Controllers\Api\ManagerController::class, 'register']);
Route::post('/manager/login', [\App\Http\Controllers\Api\ManagerController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('create-task', [TaskController::class, 'createTask']);
    Route::get('show-tasks', [TaskController::class, 'showTasks']);
    Route::get('show-task/{id}', [TaskController::class, 'showTask']);
    Route::post('{taskId}/addComment', [UserController::class, 'addComment']);

});

Route::middleware(['auth:manager'])->post('manager/{taskId}/addComment', [\App\Http\Controllers\Api\ManagerController::class, 'addComment']);
