<?php

use App\Http\Controllers\BasicLoginController;
use App\Http\Controllers\PublicKeyController;
use App\Http\Controllers\UsersController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UsersController::class, 'index']);
Route::post('/users', [UsersController::class, 'store']);

Route::get('/users/by_email/{email}', [UsersController::class, 'show_by_email']);
Route::get('/users/by_id/{id}', [UsersController::class, 'show_by_id']);
