<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function indexAction($google_id)
    {

        $calendar = Calendar::where('google_id', $google_id)->with('events')->with('user')->first();

        $calendar = $calendar ? $calendar : '404';

        // Get Calendar Updated Date by Events updated date
        $updated = NULL;
        foreach ($calendar->events as $event) {
            $date = $updated;
            if (Carbon::parse($event->updated_data_at) < Carbon::parse($date)) {
                $date = Carbon::parse($event->updated_data_at);
                $date = $date->format('m/d/Y');
            }
            $updated = $date;

            $location = json_decode($event->location);
            if ($location) {
                $event->location = $location->route.', '.$location->country;
            }
        }
        $calendar->updated = $updated;

//        var_dump($calendar);
//        exit;

        return view('frontend.calendar', ['calendar' =>$calendar]);
    }

}
