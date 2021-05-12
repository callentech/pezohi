<?php

namespace App\Http\Controllers;

use App\Mail\EventStatusNotify;

use App\Mail\SharedCalendarNotify;
use App\Models\Calendar;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $event
     * @param $action
     */
    protected function sendMainNotify($event, $action)
    {
        $params = [
            'calendar' => $event->calendar,
            'event' => $event,
            'action' => $action,
            'dateTime' => now()
        ];
        $calendars = Calendar::where('google_id', $event->calendar->google_id)->get();
        foreach ($calendars as $calendar) {
            $subscribes = Subscribe::where('calendar_id', $calendar->id)->get();
            foreach ($subscribes as $subscribe) {
                $user = User::find($subscribe->user_id);
                Mail::to($user->email)->send(new EventStatusNotify($params));
            }
            Mail::to($calendar->user->email)->send(new EventStatusNotify($params));
        }
    }

    /**
     * @param $url
     * @param $email
     */
    protected function sendSharedCalendarNotify($url, $email)
    {
        $params = [
            'url' => $url,
            'email' => $email,
            'dateTime' => now()
        ];
        Mail::to($email)->send(new SharedCalendarNotify($params));
    }

    /**
     * @param $url
     * @param $phone
     * @throws ConfigurationException
     * @throws TwilioException
     */
    protected function sendSharedCalendarSms($url, $phone)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $client = new Client($sid, $token);
        $number = $phone;
        $message = 'Pezohi | Calendar Was Shared for You. URL: '.$url;
        $client->messages->create($number, ['from' => $from, 'body' => $message]);
    }
}
