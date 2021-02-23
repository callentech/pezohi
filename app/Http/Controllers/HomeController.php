<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Str;

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

        $calendarsData = $service->calendarList->listCalendarList();

        $calendarsItems = $calendarsData->getItems();

        $calendars = [];

        foreach ($calendarsItems as $calendar) {

            // Exclude Birthdays && Holidays Calendars
            if (Str::contains($calendar->id, ['#holiday@group.v.calendar.google.com']) || Str::contains($calendar->id, ['addressbook#contacts@group.v.calendar.google.com'])) {
                continue;
            }

           

            $eventsData = $service->events->listEvents($calendar->id);

            $calendar->events = $eventsData->getItems();


            


            $calendars[] = $calendar;
        }


        
        /*
      


        foreach ($calendars as &$calendar) {

            // Exclude Birthdays && Holidays Calendars

            if (Str::contains($calendar->id, ['#holiday@group.v.calendar.google.com'])) {
                
                unset($calendar);

                continue;
            }
            // var_dump($calendar->id);

            // var_dump(Str::contains($calendar->id, ['#holiday@group.v.calendar.google.com']));

            // if (Str::contains($calendar->id, ['#holiday@group.v.calendar.google.com']) || Str::contains($calendar->id, ['addressbook#contacts@group.v.calendar.google.com'])) {
            //     continue;
            // }
            
            $calendarData = $service->calendars->get($calendar->id);

            $eventsData = $service->events->listEvents($calendar->id);

            $calendar->events = $eventsData->getItems();

            
            
            foreach ($calendar->events as $event) {

                $dateTime = $event->start->date ? $event->start->date : $event->start->dateTime;
                $startDateTime = new \DateTime($dateTime);

                $dateTime = $event->end->date ? $event->end->date : $event->end->dateTime;
                $endDateTime = new \DateTime($dateTime);

                $event->startDate = date_format($startDateTime, 'd/m/Y');
                $event->startTime = date_format($startDateTime, 'h:i A');

                $event->endDate = date_format($endDateTime, 'd/m/Y');
                $event->endTime = date_format($endDateTime, 'h:i A');
            }



           

            $calendar->lastUpdated = count($calendar->events) > 0 ? $calendar->events[0]->updated : NULL;

            foreach ($calendar->events as $event) {
                if ($event->updated < $calendar->lastUpdated) {
                    $calendar->lastUpdated = $event->updated;
                }
            }

            $now = new \DateTime;
            $ago = new \DateTime($calendar->lastUpdated);
            $diff = $now->diff($ago);

            if ($diff->y > 0 || $diff->m > 0 || $diff->d > 2 || $diff->h > 48) {
                $calendar->lastUpdated = $calendar->lastUpdated ? date_format($ago, 'd.m.Y') : NULL;
            } else {
                $calendar->lastUpdated = $calendar->lastUpdated ? \Carbon\Carbon::createFromTimeStamp(strtotime($calendar->lastUpdated))->diffForHumans() : NULL;
            }

        }

        */

        return view('home', ['calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE)]);
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
            'code' => 1,
            'data' => [
                'createdCalendar' => $createdCalendar,
                'message' => 'Calendar created successfully'
            ]
        ], JSON_UNESCAPED_UNICODE);

    }

    public function addNewCalendarEventAction(Request $request)
    {
       
        // var_dump($request->all());
        // exit;
/*
        array(6) {
          ["_token"]=>              string(40) "WVqtwRasXCAXTMcZeLiGxg6fb9ZM48juq7zn0cL1"
          ["calendar_id"]=>         string(52) "dvu11sq4uvbi9k9inpc5ggt9t8@group.calendar.google.com"
          ["new-event-datetime"]=>  string(10) "01.02.2021"
          ["new-event-address"]=>   string(7) "Address"
          ["new-event-type"]=>      string(4) "game"
          ["new-event-notes"]=>     string(5) "Notes"
        }

        */

        session_start();
        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'calendar_id' => 'required',
            'new_event_datetime' => 'required',
            'new_event_address' => 'required',
            'new_event_type' => 'required',
            'new_event_notes' => 'required'
        ]);

        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));

        $service = new Google_Service_Calendar($this->client);


        $adminEmails = \DB::table('users')
            ->where('role', 'admin')
            ->pluck('email');

        $attendees = [];
        foreach ($adminEmails as $email) {
            $attendees[] = ['email' => $email];
        }

        
        $date = new \DateTime($request->new_event_datetime);

        $event = new \Google_Service_Calendar_Event([
            'summary' => 'Event',
            'location' => $request->new_event_address,
            'description' => $request->new_event_notes,
            
            'start' => [
                'dateTime' => date_format($date, 'c'),
                'timeZone' => date_format($date, 'e'),
            ],
            
            'end' => [
                'dateTime' => date_format($date, 'c'),
                'timeZone' => date_format($date, 'e'),
            ],

            'extendedProperties' => [
                'private' => [
                    'type' => $request->new_event_type
                ]
            ],
            
            
            'attendees' => $attendees,
            
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ],
            ],
        ]);

        $calendarId = $request->calendar_id;
        $event = $service->events->insert($calendarId, $event);
        // printf('Event created: %s\n', $event->htmlLink);



        return json_encode([
            'code' => 1,
            'data' => [
                'event' => $event,
                'message' => 'Event create success'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    public function editCalendarAction(Request $request) 
    {
        session_start();
        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'calendar_id' => 'required',
            'calendar_name' => 'required'
        ]);

        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));
        $service = new Google_Service_Calendar($this->client);

        $calendar = $service->calendars->get($request->calendar_id);
        $calendar->setSummary($request->calendar_name);

        $updatedCalendar = $service->calendars->update($request->calendar_id, $calendar);

        return json_encode([
            'code' => 1,
            'data' => [
                'updatedCalendar' => $updatedCalendar,
                'message' => 'Data updated successfully'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }


    public function getCalendarDataAction(Request $request)
    {
        session_start();
        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'calendar_id' => 'required'
        ]);

        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));
        $service = new Google_Service_Calendar($this->client);

        $calendarData = $service->calendars->get($request->calendar_id);
        $calendarEvents = $service->events->listEvents($request->calendar_id);
        
        // foreach ($calendarEvents->items as $event) {
        //     if ($event->location) {
        //         $location = substr($event->location, 0, 30);
        //         $location = rtrim($location, "!,.-");
        //         $location = substr($location, 0, strrpos($location, ' '));
        //         $location .= '...';
        //         $event->location = $location;
        //     }

        //     if ($event->description) {
        //         $description = substr($event->description, 0, 30);
        //         $description = rtrim($description, "!,.-");
        //         $description = substr($description, 0, strrpos($description, ' '));
        //         $description .= '...';
        //         $event->description = $description;
        //     }
        // }
       
        return json_encode([
            'code' => 1,
            'data' => [
                'calendarData' => $calendarData,
                'calendarEvents' => $calendarEvents
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    public function deleteCalendarAction(Request $request)
    {
        session_start();
        if (!isset($_SESSION['googleClientToken']) || !$_SESSION['googleClientToken']) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $calendarId = $request->calendar_id;
        if (!$calendarId || $calendarId == '') {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));
        $service = new Google_Service_Calendar($this->client);

        try {
            $service->calendars->delete($calendarId);
        } catch (\Exception $ex) {
            return json_encode([
                'code' => 0
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'code' => 1,
            'data' => [
                'message' => 'Calendar deleted successfully'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

}
