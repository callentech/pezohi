<?php

namespace App\Http\Controllers;

use App\Mail\EventStatusNotify;
use App\Models\Calendar;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;

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
}
