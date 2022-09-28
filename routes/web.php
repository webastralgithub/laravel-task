<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
Route::get('edit/{id}', [UserController::class, 'show'])->middleware(['auth'])->name('edit');
Route::patch('update/{id}', [UserController::class, 'update'])->middleware(['auth'])->name('update');
Route::get('delete/{id}', [UserController::class, 'destroy'])->middleware(['auth'])->name('delete');

require __DIR__.'/auth.php';
