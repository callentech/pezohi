<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCalendars;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Session;

use Google_Client;
use Google_Service_Calendar;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->scopes([Google_Service_Calendar::CALENDAR])->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return RedirectResponse
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        session_start();

        try {
            $scopes = array(
                'https://www.googleapis.com/auth/plus.business.manage'
            );
            $parameters = ['access_type' => 'offline', "prompt" => "consent select_account"];
            $account = Socialite::driver('google')->scopes($scopes)->with($parameters)->user();

            $user = User::updateOrCreate(
                [
                    'google_id' => $account->id
                ],
                [
                    'name' => $account->name,
                    'email' => $account->email,
                    'google_id'=> $account->id,
                    'google_access_token' => $account->token,
                    'password' => encrypt('123456dummy')
                ]
            );

            Auth::login($user);

            $this->dispatch(new SyncCalendars(Auth::user()));
            $previous   = Session::get('backUrl');
            return $previous ? redirect($previous) : redirect()->intended('home');

        } catch (Exception $e) {
            Auth::logout();
            return redirect()->route('login');
        }
    }



}
