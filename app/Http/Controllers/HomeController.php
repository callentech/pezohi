<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;


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
        $calendars = Auth::user()->calendars()->with('events')->get();
        foreach ($calendars as $key => $calendar) {
            if ($calendar->google_id == Auth::user()->email) {
                unset($calendars[$key]);;
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
            }
            $calendar->updated = $updated;

//          $calendar->publicUrl = 'https://calendar.google.com/calendar/embed?src='.$calendar->google_id.'&ctz='.$calendar->timezone;
            $calendar->publicUrl = url('/').'/calendar/'.$calendar->google_id;
        }
        $jobsStatus = Auth::user()->jobs_status;
        return view('home', ['calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE), 'jobs_status' => $jobsStatus]);
    }
}
