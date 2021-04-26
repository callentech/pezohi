<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Subscribe;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isJson;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Application|Factory|View|RedirectResponse
     */
    public function index()
    {
        $calendars = [];

        $usrCalendars = Auth::user()->calendars()->with('events')->get();
        foreach ($usrCalendars as $item) {
            $item->owned = TRUE;
            $calendars[] = $item;
        }
        unset($item);

        $usrSubscribes = Auth::user()->subscribes()->with('calendar')->get();
        foreach ($usrSubscribes as $item) {
            if ($item->calendar->user->id != Auth::user()->id) {
                $item->calendar->owned = FALSE;
                $calendars[] = $item->calendar;
            }
        }
        unset($item);



        foreach ($calendars as $key => $calendar) {
            if ($calendar->google_id == Auth::user()->email) {
                unset($calendars[$key]);
            }
            $calendar->eventsCount = count($calendar->events);

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
            }
            $calendar->updated = $updated;
            $calendar->publicUrl = url('/').'/calendar/'.$calendar->google_id;

            $calendar->owner = $calendar->user->email;


            // Is subscribe
            $subscribe = Subscribe::where(['user_id' => Auth::user()->id, 'calendar_id' => $calendar->id])->first();
            $calendar->isSubscribe = $subscribe ? TRUE : FALSE;

        }
        $jobsStatus = Auth::user()->jobs_status;
        return view('home', ['calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE), 'jobs_status' => $jobsStatus]);
    }

    private function isJSON($string){
       return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}
