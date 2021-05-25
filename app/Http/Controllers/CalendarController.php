<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Calendar;
use App\Models\Subscribe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function indexAction($google_id)
    {
        $calendar = Calendar::where('google_id', $google_id)->with('events')->with('user')->first();
        if (!$calendar) {
            return view('frontend.calendar', ['calendar' => '404', 'user' => Auth::user() ? Auth::user() : "{'status':'anonimous'}" ]);
        }

        // Get Calendar Updated Date by Events updated date
        $updated = NULL;
        foreach ($calendar->events as $event) {
            $date = $updated;
            if (Carbon::parse($event->updated_data_at) < Carbon::parse($date)) {
                $date = Carbon::parse($event->updated_data_at);
                $date = $date->format('m/d/Y');
            }
            $updated = $date;

            if ($this->isJSON($event->location)) {
                $location = json_decode($event->location);
                $event->location = $location->route.', '.$location->country;
            }

            $event->attendee = FALSE;
            if (Auth::user())  {
                $attendee = Attendee::where(['user_id' => Auth::user()->id, 'event_id' => $event->id])->first();
                $event->attendee = (bool)$attendee;
            }
        }
        $calendar->updated = $updated;

        // Subscribe / Unsubscribe status
        $calendar->isSubscribed = FALSE;
        if (Auth::user()) {
            $subscribe = Subscribe::where(['user_id' => Auth::user()->id, 'calendar_id' => $calendar->id])->first();
            $calendar->isSubscribed = $subscribe ? TRUE : FALSE;
        }

        $calendar->publicUrl = url('/').'/calendar/'.$calendar->google_id;
        return view('frontend.calendar', ['calendar' =>$calendar, 'user' => Auth::user() ? Auth::user() : "{'status':'anonimous'}"]);
    }

    private function isJSON($string): bool
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

}
