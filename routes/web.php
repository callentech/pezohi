<?php

use Illuminate\Support\Facades\Auth;
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
Route::get('/calendar/{google_id}', [App\Http\Controllers\CalendarController::class, 'indexAction'])->name('calendar');


Route::get('/terms', function () { return view('welcome'); });

Route::get('auth/google', [App\Http\Controllers\GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [App\Http\Controllers\GoogleController::class, 'handleGoogleCallback'])->name('authGoogleCallback');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


/* Calendars Actions */
Route::post('/new-calendar', [App\Http\Controllers\CalendarsController::class, 'addCalendarAction'])->name('addCalendar');
Route::post('/edit-calendar', [App\Http\Controllers\CalendarsController::class, 'editCalendarAction'])->name('editCalendar');
Route::get('/get-calendar-data', [App\Http\Controllers\CalendarsController::class, 'getCalendarDataAction'])->name('getCalendarData');
Route::post('/delete-calendar', [App\Http\Controllers\CalendarsController::class, 'deleteCalendarAction'])->name('deleteCalendar');


/* Events Actions */
//Route::post('/new-event', [App\Http\Controllers\EventsController::class, 'addEventAction'])->name('addEvent');
Route::post('/new-single-event', [App\Http\Controllers\EventsController::class, 'addSingleEventAction'])->name('addSingleEvent');
Route::post('/edit-single-event', [App\Http\Controllers\EventsController::class, 'editSingleEventAction'])->name('editSingleEvent');

/* Admin */
Route::get('/admin-home', [App\Http\Controllers\AdminController::class, 'indexAction'])->name('adminHome')->middleware('admin');
Route::post('/admin-set-user-role', [App\Http\Controllers\AdminController::class, 'setUserRoleAction'])->name('adminSetUserRole')->middleware('admin');
//Route::get('/admin-users', [App\Http\Controllers\AdminController::class, 'usersAction'])->name('adminUsers');

/*
Route::post('/calendar-edit', [App\Http\Controllers\HomeController::class, 'editCalendarAction'])->name('editCalendar');

Route::post('/calendar-delete', [App\Http\Controllers\HomeController::class, 'deleteCalendarAction'])->name('deleteCalendar');
Route::post('/calendar-get-data', [App\Http\Controllers\HomeController::class, 'getCalendarDataAction']);

*/

















Route::post('google/webhook', [App\Http\Controllers\GoogleWebhookController::class])->name('google.webhook');
//Route::name('google.webhook')->post('google/webhook', 'GoogleWebhookController');




// Route::get('/calendar', [App\Http\Controllers\GoogleController::class, 'calendar']);
//Route::get('/oauth', [App\Http\Controllers\GCalendarController::class, 'oauth'])->name('oauthCallback');


//Route::get('oauth', ['as' => 'oauthCallback', 'uses' => [App\Http\Controllers\GCalendarController::class, 'index']]);



// Route::get('/google-calendar/connect', [App\Http\Controllers\GoogleController::class, 'connect']);
