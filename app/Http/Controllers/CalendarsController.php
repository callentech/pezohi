<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCalendars;
use App\Models\Calendar;
use App\Services\Google;
use Google_Service_Calendar_Calendar;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class CalendarsController extends Controller
{
    public function addNewCalendarAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'calendar_name' => 'required',
            'owner_email_address' => 'required'
        ]);

        $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary(trim($request->calendar_name));
        $calendar->setTimeZone(config('app.timezone'));
        $createdGoogleCalendar = $service->calendars->insert($calendar);

        $createdCalendar = new Calendar([
            'google_id' => $createdGoogleCalendar->id,
            'access_role' => 'owner',
            'name' => $createdGoogleCalendar->summary,
            'color' => '#ac725e',
            'timezone' => $createdGoogleCalendar->timeZone,
        ]);

        Auth::user()->calendars()->save($createdCalendar);

        return response()->json([
            'code' => 1,
            'data' => [
                'createdCalendar' => $createdCalendar,
                'message' => 'Calendar created successfully'
            ]
        ]);
    }

    public static function jobsAction()
    {
        var_dump(Auth::user());

        exit;
        $result = dispatch(new SyncCalendars());
    }


    /*
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
     * */
}
