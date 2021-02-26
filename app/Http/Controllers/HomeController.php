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

        try {
            $this->client->setAccessToken(json_encode($this->googleClientToken));
            $service = new Google_Service_Calendar($this->client);
            $calendarsData = $service->calendarList->listCalendarList();
        } catch (\Exception $ex) {
            Auth::logout();
            return redirect()->route('home');
        }
        
        $calendarsItems = $calendarsData->getItems();
        $calendars = [];

        foreach ($calendarsItems as $calendar) {

            // Exclude Primary, Birthdays && Holidays Calendars
            if (Str::contains($calendar->id, ['#holiday@group.v.calendar.google.com']) || Str::contains($calendar->id, ['addressbook#contacts@group.v.calendar.google.com']) || $calendar->id == Auth::user()->email) {
                continue;
            }

           
            $eventsData = $service->events->listEvents($calendar->id);
            $calendar->events = $eventsData->getItems();
            $calendar->eventsCount = count($calendar->events);

            $calendar->last_updated = NULL;
            if (count($calendar->events) > 0) {
                $calendar->last_updated = $calendar->events[0]->updated;
                foreach ($calendar->events as $event) {
                    if ($calendar->last_updated < $event->updated) {
                        $calendar->last_updated = $event->updated;
                    }
                }
            }

            $calendars[] = $calendar;
        }

        return view('home', ['calendars' => json_encode($calendars, JSON_UNESCAPED_UNICODE)]);
    }

    private function cuteDate($date)
    {
        $today = date('d.m.Y', time());
        $yesterday = date('d.m.Y', time() - 86400);
        $dbDate = date('d.m.Y', strtotime($date));
        $dbTime = date('H:i', strtotime($date));
        $dbHours = date('H', strtotime($date));
        $dbMeridiem = date('A', strtotime($date));

        switch ($dbDate)
        {
          //case $today : $output = 'Today, '. $dbTime .' '. $dbMeridiem; break;
          case $today : 
            $output = $dbHours.' hours ago';
            break;

          case $yesterday : 
            $output = 'Yesterday, '. $dbTime .' '. $dbMeridiem;
            break;

          default : $output = $dbDate;
        }
        return $output;

        //Yesterday, HH:MM hours AM/PM 
    }

    public function addCalendarEventAction(Request $request)
    {
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

        $date = new \DateTime($request->new_event_datetime);

        $adminEmails = \DB::table('users')->where('role', 'admin')->pluck('email');
        $attendees = [];
        foreach ($adminEmails as $email) {
            $attendees[] = ['email' => $email];
        }

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

        return json_encode([
            'code' => 1,
            'data' => [
                'event' => $event,
                'message' => 'Event created successfully'
            ]
        ], JSON_UNESCAPED_UNICODE);
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

        // Add new events
        $events = json_decode($request->events, TRUE);
        if ($events && count($events) > 0) {
            foreach ($events as $event) {
                if ($event['id'] == 'new') {

                    $date = new \DateTime($event['start']['dateTime']);

                    $adminEmails = \DB::table('users')->where('role', 'admin')->pluck('email');
                    $attendees = [];
                    foreach ($adminEmails as $email) {
                        $attendees[] = ['email' => $email];
                    }

                    $event = new \Google_Service_Calendar_Event([
                        'summary' => 'Event',
                        'location' => $event['location'],
                        'description' => $event['description'],
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
                                'type' => $event['extendedProperties']['private']['type']
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

                    $calendarId = $createdCalendar->id;
                    $event = $service->events->insert($calendarId, $event);
                }
            }
        }
        

        return json_encode([
            'code' => 1,
            'data' => [
                'createdCalendar' => $createdCalendar,
                'message' => 'Calendar created successfully'
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

        // Add new events
        $events = json_decode($request->events, TRUE);
        foreach ($events as $event) {
            if ($event['id'] == 'new') {

                $date = new \DateTime($event['start']['dateTime']);

                $adminEmails = \DB::table('users')->where('role', 'admin')->pluck('email');
                $attendees = [];
                foreach ($adminEmails as $email) {
                    $attendees[] = ['email' => $email];
                }

                $event = new \Google_Service_Calendar_Event([
                    'summary' => 'Event',
                    'location' => $event['location'],
                    'description' => $event['description'],
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
                            'type' => $event['extendedProperties']['private']['type']
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
            }
        }

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
                'code' => 10
            ], JSON_UNESCAPED_UNICODE);
        }

        $calendarId = $request->calendar_id;

        if (!$calendarId || $calendarId == '') {
            return json_encode([
                'code' => 20
            ], JSON_UNESCAPED_UNICODE);
        }

        $this->googleClientToken = $_SESSION['googleClientToken'];
        $this->client->setAccessToken(json_encode($this->googleClientToken));
        $service = new Google_Service_Calendar($this->client);

        // $calendarData = $service->calendars->get($request->calendar_id);
        // var_dump($calendarData);
        // exit;
        // $calendarEvents = $service->events->listEvents($request->calendar_id);


        try {
            $service->calendars->delete($calendarId);
        } catch (\Exception $ex) {
            return json_encode([
                'code' => 30,
                'message' => $ex->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'code' => 1,
            'data' => [
                'message' => 'Calendar deleted successfully'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    public function addNewCalendarEventAction(Request $request)
    {
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

        return json_encode([
            'code' => 1,
            'data' => [
                'event' => $event,
                'message' => 'Event create success'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
