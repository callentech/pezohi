<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function indexAction($google_id)
    {

        $calendar = Calendar::where('google_id', $google_id)->with('events')->with('user')->first();

        $calendar = $calendar ? $calendar : '404';

//        var_dump($calendar);
//        exit;

        return view('frontend.calendar', ['calendar' =>$calendar]);
    }

}
