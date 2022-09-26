<?php

use App\Http\Controllers\DerivativeController;
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

Route::get('derivative', [DerivativeController::class, 'index'])->name('derivative.index');
Route::get('derivative/{id}', [DerivativeController::class, 'show'])->name('derivative.show');
