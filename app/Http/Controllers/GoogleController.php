<?php

namespace App\Http\Controllers;

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
     * @return void
     */
    public function handleGoogleCallback()
    {

        session_start();

        try {

            //$user = Socialite::driver('google')->stateless()->user();

            $account = Socialite::driver('google')
            // ->scopes(['openid', 'profile', 'email', \Google_Service_People::CONTACTS_READONLY])
            ->scopes(['openid', 'profile', 'email'])
            ->with(["access_type" => "offline", "prompt" => "consent select_account"])
            ->user();

            // Set token for the Google API PHP Client
            // $_SESSION['googleClientToken'] = [
            //     'access_token' => $account->token,
            //     'refresh_token' => $account->refreshToken,
            //     'expires_in' => $account->expiresIn
            // ];


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

//            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//            \DB::table('users')->truncate();
//
//            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');


            Auth::login($user);



            // $finduser = User::where('google_id', $user->id)->first();
            // if ($finduser) {
            //     Auth::login($finduser);
            // } else {
            //     $newUser = User::create([
            //         'name' => $user->name,
            //         'email' => $user->email,
            //         'role' => 'owner',
            //         'google_id'=> $user->id,
            //         'password' => encrypt('123456dummy')
            //     ]);
            //     Auth::login($newUser);
            // }

            return redirect()->intended('home');

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }



}
