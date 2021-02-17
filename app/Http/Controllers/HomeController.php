<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;

use Auth;
use Google_Client;
use Google_Service_Calendar;

class HomeController extends Controller
{

    protected $client;
    
    protected $googleClientToken;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->client = new Google_Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setDeveloperKey(env('GOOGLE_API_KEY'));
        //$this->client->setAccessType('offline');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            Auth::logout();
            return redirect()->route('home');
        }

        $this->googleClientToken = $_SESSION['googleClientToken'];

        $this->client->setAccessToken(json_encode($this->googleClientToken));
        
        $service = new Google_Service_Calendar($this->client);
        $calendars  = $service->calendarList->listCalendarList();
        foreach ($calendars->items as $calendar) {

            $calendarData = $service->calendars->get($calendar->id);

            $results = $service->events->listEvents($calendar->id);
            $calendar->events = $results->getItems();

            $calendar->lastUpdated = count($calendar->events) > 0 ? $calendar->events[0]->updated : NULL;

            foreach ($calendar->events as $event) {
                if ($event->updated < $calendar->lastUpdated) {
                    $calendar->lastUpdated = $event->updated;
                }
            }

            $date = new \DateTime($calendar->lastUpdated);
            $calendar->lastUpdated = $calendar->lastUpdated ? date_format($date, 'd.m.Y') : NULL;
        }

        return view('home', ['calendars' => json_encode($calendars->items, JSON_UNESCAPED_UNICODE)]);
    }

    public function addNewCalendarAction(Request $request)
    {
        session_start();


        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'calendar_name' => 'required',
            'owner_email_address' => 'required'
        ]);

        // Create New Calendar
        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));

        $service = new Google_Service_Calendar($this->client);
        $calendar = new \Google_Service_Calendar_Calendar();

        $calendarSummary = trim($request->calendar_name);
       
        $calendar->setSummary($calendarSummary);
        $calendar->setTimeZone(config('app.timezone'));

        $createdCalendar = $service->calendars->insert($calendar);

        return json_encode([
            'code' => 1
        ], JSON_UNESCAPED_UNICODE);

    }

   
}
