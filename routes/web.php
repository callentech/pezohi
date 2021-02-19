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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/terms', function () { return view('welcome'); });

Route::get('auth/google', [App\Http\Controllers\GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [App\Http\Controllers\GoogleController::class, 'handleGoogleCallback'])->name('authGoogleCallback');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/calendar-new', [App\Http\Controllers\HomeController::class, 'addNewCalendarAction'])->name('addNewCalendar');
Route::post('/calendar-new-event', [App\Http\Controllers\HomeController::class, 'addNewCalendarEventAction'])->name('addNewCalendarEvent');




// Route::get('/calendar', [App\Http\Controllers\GoogleController::class, 'calendar']);
//Route::get('/oauth', [App\Http\Controllers\GCalendarController::class, 'oauth'])->name('oauthCallback');


//Route::get('oauth', ['as' => 'oauthCallback', 'uses' => [App\Http\Controllers\GCalendarController::class, 'index']]);



// Route::get('/google-calendar/connect', [App\Http\Controllers\GoogleController::class, 'connect']);