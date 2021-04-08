<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCalendars;
use App\Mail\EventStatusNotify;
use App\Models\Calendar;
use App\Models\Event;
use App\Services\Google;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_CalendarListEntry;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class CalendarsController extends Controller
{

    /**
     * @param $event
     * @param $action
     */
    private function sendMainNotify($event, $action)
    {
        $params = [
            'calendar' => $event->calendar,
            'event' => $event,
            'action' => $action,
            'dateTime' => now()
        ];
        $calendars = Calendar::where('google_id', $event->calendar->google_id)->get();
        foreach ($calendars as $calendar) {
            Mail::to($calendar->user->email)->send(new EventStatusNotify($params));
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function newCalendarAction(Request $request): JsonResponse
    {
        $request->validate([
            'calendar_name' => 'required'
        ]);

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $calendar = new Google_Service_Calendar_Calendar();
            $calendar->setSummary(trim($request->calendar_name));
            $calendar->setDescription('#pezohi | PEZOHI');
            $calendar->setTimeZone(config('app.timezone'));
            $createdGoogleCalendar = $service->calendars->insert($calendar);

            // Save calendar into DB
            $createdCalendar = new Calendar([
                'google_id' => $createdGoogleCalendar->id,
                'access_role' => 'owner',
                'name' => $createdGoogleCalendar->summary,
                'description' => $createdGoogleCalendar->description,
                'color' => '#FFFFFF',
                'timezone' => $createdGoogleCalendar->timeZone
            ]);
            Auth::user()->calendars()->save($createdCalendar);

            // Create events
            $newEvents = json_decode($request->events, TRUE);
            foreach ($newEvents as $event) {

                // Save new event data to Google Calendar
                $started_at = date_create($event['started_at']);
                $ended_at = date_create($event['ended_at']);

                $newGoogleEvent = new Google_Service_Calendar_Event(array(
                    'summary' => 'Pezohi Event',
                    'location' => trim($event['location']),
                    'description' => trim($event['description']),
                    'start' => array(
                        'dateTime' => date_format($started_at, 'c'),
                        'timeZone' => config('app.timezone')
                    ),
                    'end' => array(
                        'dateTime' => date_format($ended_at, 'c'),
                        'timeZone' => config('app.timezone')
                    ),
                ));

                $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
                $extendedProperties->setPrivate(['type' => $event['type']]);
                $newGoogleEvent->setExtendedProperties($extendedProperties);
                $newGoogleEvent = $service->events->insert($createdGoogleCalendar->id, $newGoogleEvent);

                // Save new event data to DB
                $newEvent = new Event([
                    'google_id' => $newGoogleEvent->id,
                    'name' => $newGoogleEvent->summary,
                    'type' => $newGoogleEvent->extendedProperties->getPrivate()['type'],
                    'description' => $newGoogleEvent->description,
                    'location' => $newGoogleEvent->location,
                    'allday' => 0,
                    'started_at' => Carbon::parse($newGoogleEvent->start->dateTime)->setTimezone($newGoogleEvent->start->timeZone),
                    'ended_at' => Carbon::parse($newGoogleEvent->end->dateTime)->setTimezone($newGoogleEvent->end->timeZone),
                    'updated_data_at' => Carbon::parse($newGoogleEvent->updated)->setTimezone($newGoogleEvent->start->timeZone)
                ]);
                $createdCalendar->events()->save($newEvent);
            }
            return response()->json([
                'code' => 1,
                'data' => [
                    'createdCalendar' => $createdCalendar,
                    'message' => 'Calendar created successfully'
                ]
            ]);
        } catch(\Exception $ex) {
            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getErrors()[0]['message']) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => $ex->getErrors()[0]['message']
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 0,
                ]);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCalendarDataAction(Request $request): JsonResponse
    {
        $request->validate([
            'calendar_id' => 'required'
        ]);

        $calendar = Calendar::with('events')->with('user')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        foreach ($calendar->events as $event) {
            if (Carbon::parse($event->ended_at) < Carbon::now()) {
                $event->status = 'over';
            }
        }

        return response()->json([
            'code' => 1,
            'data' => [
                'calendarData' => $calendar
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function editCalendarAction(Request $request): JsonResponse
    {
        $request->validate([
            'calendar_id' => 'required',
            'calendar_name' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0,
                'data' => [
                    'message' => 'Google calendar not found'
                ]
            ]);
        }
        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $googleCalendar = $service->calendars->get($calendar->google_id);
            $googleCalendar->setSummary($request->calendar_name);
            $updatedGoogleCalendar = $service->calendars->update($calendar->google_id, $googleCalendar);

            // Update calendar data in DB
            $calendar->name = $updatedGoogleCalendar->summary;
            $calendar->save();

            // Sync events Data
            $editedEvents = json_decode($request->events, TRUE);
            foreach ($editedEvents as $event) {

                if (isset($event['status']) && $event['status'] == 'deleted') {
                    $service->events->delete($updatedGoogleCalendar->id, $event['google_id']);
                    $deletedEvent = Event::find($event['id']);

                    // Send Mail to Calendar Shared and Owned users
                    $this->sendMainNotify($event, 'Deleted');
                    $deletedEvent->delete();
                } else if (isset($event['status']) && $event['status'] == 'cancelled') {
                    $googleEvent = $service->events->get($updatedGoogleCalendar->id, $event['google_id']);
                    $googleEvent->setStatus('cancelled');
                    $updatedEvent = Event::find($event['id']);
                    $updatedEvent->status = 'cancelled';

                    // Send Mail to Calendar Shared and Owned users
                    $this->sendMainNotify($updatedEvent, 'Cancelled');
                    $updatedEvent->save();
                } else {

                    if ($event['id'] == 'new') {

                        // Create ne event
                        $started_at = date_create($event['started_at']);
                        $ended_at = date_create($event['ended_at']);
                        $newGoogleEvent = new Google_Service_Calendar_Event(array(
                            'summary' => 'Pezohi Event',
                            'location' => trim($event['location']),
                            'description' => trim($event['description']),
                            'start' => array(
                                'dateTime' => date_format($started_at, 'c'),
                                'timeZone' => config('app.timezone')
                            ),
                            'end' => array(
                                'dateTime' => date_format($ended_at, 'c'),
                                'timeZone' => config('app.timezone')
                            ),
                        ));
                        $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
                        $extendedProperties->setPrivate(['type' => $event['type']]);
                        $newGoogleEvent->setExtendedProperties($extendedProperties);
                        $newGoogleEvent = $service->events->insert($updatedGoogleCalendar->id, $newGoogleEvent);

                        // Save Event Data into DB
                        $newEvent = new Event([
                            'google_id' => $newGoogleEvent->id,
                            'name' => $newGoogleEvent->summary,
                            'type' => $newGoogleEvent->extendedProperties->getPrivate()['type'],
                            'description' => $newGoogleEvent->description,
                            'location' => $newGoogleEvent->location,
                            'status' => $newGoogleEvent->status,
                            'allday' => 0,
                            'started_at' => Carbon::parse($newGoogleEvent->start->dateTime)->setTimezone($newGoogleEvent->start->timeZone),
                            'ended_at' => Carbon::parse($newGoogleEvent->end->dateTime)->setTimezone($newGoogleEvent->end->timeZone),
                            'updated_data_at' => Carbon::parse($newGoogleEvent->updated)->setTimezone($newGoogleEvent->start->timeZone)
                        ]);
                        $calendar->events()->save($newEvent);
                    } else {

                        // Update exists event
                        $started_at = date_create($event['started_at']);
                        $ended_at = date_create($event['ended_at']);

                        $updatedEvent = $service->events->get($updatedGoogleCalendar->id, $event['google_id']);
                        $updatedEvent->location = $event['location'];
                        $updatedEvent->description = $event['description'];
                        $updatedEvent->start = [
                            'dateTime' => date_format($started_at, 'c'),
                            'timeZone' => config('app.timezone')
                        ];
                        $updatedEvent->end = [
                            'dateTime' => date_format($ended_at, 'c'),
                            'timeZone' => config('app.timezone')
                        ];

                        $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
                        $extendedProperties->setPrivate(['type' => $event['type']]);
                        $updatedEvent->setExtendedProperties($extendedProperties);
                        $updatedEvent = $service->events->update($updatedGoogleCalendar->id, $updatedEvent->getId(), $updatedEvent);

                        // Update event data in DB
                        $updatedLocalEvent = Event::find($event['id']);
                        $updatedLocalEvent->name = $updatedEvent->summary;
                        $updatedLocalEvent->type = $updatedEvent->extendedProperties->getPrivate()['type'];
                        $updatedLocalEvent->description = $updatedEvent->description;
                        $updatedLocalEvent->location = $updatedEvent->location;
                        $updatedLocalEvent->started_at = Carbon::parse($updatedEvent->start->dateTime)->setTimezone($updatedEvent->start->timeZone);
                        $updatedLocalEvent->ended_at = Carbon::parse($updatedEvent->end->dateTime)->setTimezone($updatedEvent->end->timeZone);
                        $updatedLocalEvent->updated_data_at = Carbon::parse($updatedEvent->updated)->setTimezone($updatedEvent->start->timeZone);

                        // Send Mail to Calendar Shared and Owned users
                        $this->sendMainNotify($updatedLocalEvent, 'Updated');
                        $updatedLocalEvent->save();
                        $calendar->touch();
                    }

                }
            }

            return response()->json([
                'code' => 1,
                'data' => [
                    'updatedCalendar' => $updatedGoogleCalendar,
                    'message' => 'Calendar data updated successfully'
                ]
            ]);

        } catch(\Exception $ex) {
            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar or event not found'
                    ]
                ]);
            } else {

                var_dump($ex->getMessage());
                exit;
                return response()->json([
                    'code' => 0,
                    'data' => [
                        'message' => 'API Request Error'
                    ]
                ]);
            }
        }
/*
        array(5) {
        ["_token"]=>
  string(40) "vF2rKZPs6fIKliNTXAITtQ7IAfT8MiQ5cThfS2bD"
        ["calendar_id"]=>
  string(2) "89"
        ["calendar_name"]=>
  string(3) "Cal"
        ["owner_email_address"]=>
  string(20) "dev.alex42@gmail.com"
        ["events"]=>
  string(4580) "[{"id":7,"calendar_id":89,"google_id":"n82tilgl1ml2q7nkrih8uu4f3k","name":"Pezohi Event","type":"game","description":"Description2","location":"Location2","status":"over","allday":0,"started_at":"2020-02-10 00:00:00","ended_at":"2020-02-10 00:00:00","updated_data_at":"2021-04-02 15:28:32","created_at":"2021-03-12T05:32:46.000000Z","updated_at":"2021-04-07T19:59:48.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}},{"id":42,"calendar_id":89,"google_id":"ans5s3mq2nllcrip9mbr2eipn0","name":"Pezohi Event","type":"practice","description":"dsfsfsdf","location":null,"status":"cancelled","allday":0,"started_at":"2021-04-02 01:00:00","ended_at":"2021-05-09 00:55:00","updated_data_at":"2021-04-06 22:08:49","created_at":"2021-04-02T19:21:13.000000Z","updated_at":"2021-04-07T19:59:49.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}},{"id":44,"calendar_id":89,"google_id":"pl7ls80sa8iq0mjv0ej09lf7o4","name":"Pezohi Event","type":"practice","description":"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the s","location":"Trondheimsveien, Oslo, Norway","status":"confirmed","allday":0,"started_at":"2021-04-06 01:00:00","ended_at":"2021-04-27 14:15:00","updated_data_at":"2021-04-06 18:00:46","created_at":"2021-04-06T08:34:57.000000Z","updated_at":"2021-04-07T19:59:49.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}},{"id":51,"calendar_id":89,"google_id":"5ahrrf6a9j6nl1jmf111ciqlgg","name":"Pezohi Event","type":"practice","description":"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the s","location":"Trondheimsveien, Oslo, Norway","status":"confirmed","allday":0,"started_at":"2021-04-06 01:00:00","ended_at":"2021-04-27 14:15:00","updated_data_at":"2021-04-06 23:26:15","created_at":"2021-04-06T23:26:15.000000Z","updated_at":"2021-04-07T19:59:49.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}},{"id":55,"calendar_id":89,"google_id":"vg7pmf0f2v63kghcaor069hie4","name":"Pezohi Event","type":"practice","description":"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the s","location":"Trondheimsveien, Oslo, Norway","status":"confirmed","allday":0,"started_at":"2021-04-06 01:00:00","ended_at":"2021-04-27 14:15:00","updated_data_at":"2021-04-06 23:31:13","created_at":"2021-04-06T23:31:13.000000Z","updated_at":"2021-04-07T19:59:50.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}},{"id":58,"calendar_id":89,"google_id":"7ovf5f9llr42rdl40bgm36sbo8","name":"Pezohi Event","type":"game","description":"sdf","location":"Daftary Road, Malkani Estate, Shivaji Nagar, Pratap Nagar, Malad East, Mumbai, Maharashtra, India","status":"over","allday":0,"started_at":"2021-04-07 01:00:00","ended_at":"2021-04-07 20:40:00","updated_data_at":"2021-04-06 23:38:41","created_at":"2021-04-06T23:36:27.000000Z","updated_at":"2021-04-07T19:59:50.000000Z","calendar":{"id":89,"user_id":14,"google_id":"7abuk6m5jp2i7avuukrq16unpc@group.calendar.google.com","access_role":"owner","name":"Cal","description":"#pezohi | PEZOHI","color":"#cca6ac","timezone":"UTC","created_at":"2021-03-12T05:32:44.000000Z","updated_at":"2021-04-07T19:59:50.000000Z"}}]"
}
*/

        /*




        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $googleCalendar = $service->calendars->get($calendar->google_id);
            $googleCalendar->setSummary($request->calendar_name);
            $updatedGoogleCalendar = $service->calendars->update($calendar->google_id, $googleCalendar);

            // Update calendar in DB
            $calendar->name = $updatedGoogleCalendar->summary;
            $calendar->save();

            // Sync events Data
            $editedEvents = json_decode($request->events, TRUE);

            // Delete deleted event
            foreach ($calendar->events as $event) {
                $deletedEvent = TRUE;
                foreach ($editedEvents as $editedEvent) {
                    if ($event->id == $editedEvent['id']) {
                        $deletedEvent = FALSE;
                    }
                }
                if ($deletedEvent)  {
                    $service->events->delete($updatedGoogleCalendar->id, $event->google_id);
                    $event = Event::find($event->id);

                    // Send Mail to Calendar Shared and Owned users
                    $action = 'Deleted';
                    $this->sendMainNotify($event, $action);

                    $event->delete();


                }
            }

            // Update or create events
            foreach ($editedEvents as $event) {

                if ($event['id'] == 'new') {

                    // Save new event data to Google Calendar
                    $started_at = date_create($event['started_at']);
                    $ended_at = date_create($event['ended_at']);

                    $newGoogleEvent = new Google_Service_Calendar_Event(array(
                        'summary' => 'Pezohi Event',
                        'location' => trim($event['location']),
                        'description' => trim($event['description']),
                        'start' => array(
                            'dateTime' => date_format($started_at, 'c'),
                            'timeZone' => config('app.timezone')
                        ),
                        'end' => array(
                            'dateTime' => date_format($ended_at, 'c'),
                            'timeZone' => config('app.timezone')
                        ),
                    ));

                    $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
                    $extendedProperties->setPrivate(['type' => $event['type']]);
                    $newGoogleEvent->setExtendedProperties($extendedProperties);
                    $newGoogleEvent = $service->events->insert($updatedGoogleCalendar->id, $newGoogleEvent);
                    $newEvent = new Event([
                        'google_id' => $newGoogleEvent->id,
                        'name' => $newGoogleEvent->summary,
                        'type' => $newGoogleEvent->extendedProperties->getPrivate()['type'],
                        'description' => $newGoogleEvent->description,
                        'location' => $newGoogleEvent->location,
                        'status' => $newGoogleEvent->status,
                        'allday' => 0,
                        'started_at' => Carbon::parse($newGoogleEvent->start->dateTime)->setTimezone($newGoogleEvent->start->timeZone),
                        'ended_at' => Carbon::parse($newGoogleEvent->end->dateTime)->setTimezone($newGoogleEvent->end->timeZone),
                        'updated_data_at' => Carbon::parse($newGoogleEvent->updated)->setTimezone($newGoogleEvent->start->timeZone)
                    ]);
                    $calendar->events()->save($newEvent);

                } else {

                    // Update events data

//                    if ($event->status == 'cancelled') {
//
//                    }

                    $started_at = date_create($event['started_at']);
                    $ended_at = date_create($event['ended_at']);

                    $updatedEvent = $service->events->get($updatedGoogleCalendar->id, $event['google_id']);

                    $updatedEvent->location = trim($event['location']);
                    $updatedEvent->description = trim($event['description']);
                    $updatedEvent->start = [
                        'dateTime' => date_format($started_at, 'c'),
                        'timeZone' => config('app.timezone')
                    ];
                    $updatedEvent->end = [
                        'dateTime' => date_format($ended_at, 'c'),
                        'timeZone' => config('app.timezone')
                    ];

                    $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
                    $extendedProperties->setPrivate(['type' => $event['type']]);
                    $updatedEvent->setExtendedProperties($extendedProperties);


                    // Update cancelled events

//                    if ($event->status == 'cancelled' && $updatedEvent->status != 'cancelled') {
////                        $updatedEvent->setStatus('cancelled');
//
//                        var_dump($updatedEvent->status);
//                        exit;
//                    }

                    $updatedEvent = $service->events->update($updatedGoogleCalendar->id, $updatedEvent->getId(), $updatedEvent);

                    // Update event data in DB
                    $updatedLocalEvent = Event::find($event['id']);
                    $updatedLocalEvent->name = $updatedEvent->summary;
                    $updatedLocalEvent->type = $updatedEvent->extendedProperties->getPrivate()['type'];
                    $updatedLocalEvent->description = $updatedEvent->description;
                    $updatedLocalEvent->location = $updatedEvent->location;
                    $updatedLocalEvent->started_at = Carbon::parse($updatedEvent->start->dateTime)->setTimezone($updatedEvent->start->timeZone);
                    $updatedLocalEvent->ended_at = Carbon::parse($updatedEvent->end->dateTime)->setTimezone($updatedEvent->end->timeZone);
                    $updatedLocalEvent->updated_data_at = Carbon::parse($updatedEvent->updated)->setTimezone($updatedEvent->start->timeZone);

                    $action = 'Updated';

//                    if ($event->status == 'cancelled' && $updatedEvent->status != 'cancelled') {
//                        $updatedLocalEvent->status = $updatedEvent->status;
//                        $action = 'Cancelled';
//                    }
//
//                    // Send Mail to Calendar Shared and Owned users
//                    $this->sendMainNotify($updatedLocalEvent, $action);

                    $updatedLocalEvent->save();
                    $calendar->touch();
                }
            }

            return response()->json([
                'code' => 1,
                'data' => [
                    'updatedCalendar' => $updatedGoogleCalendar,
                    'message' => 'Calendar updated successfully'
                ]
            ]);

        } catch(\Exception $ex) {

            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar or event not found'
                    ]
                ]);
            } else if ($ex->getErrors()[0]['message']) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => $ex->getErrors()[0]['message']
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 0,
                ]);
            }
        }

        */
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCalendarAction(Request $request): JsonResponse
    {
        $request->validate([
            'calendar_id' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $service->calendars->delete($calendar->google_id);

            // Delete calendar from DB
            $calendar->delete();

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Google calendar delete success'
                ]
            ]);
        } catch(\Exception $ex) {
            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar or event not found'
                    ]
                ]);
            } else if ($ex->getErrors()[0]['message']) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => $ex->getErrors()[0]['message']
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 0,
                ]);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribeCalendarAction(Request $request): JsonResponse
    {
        $request->validate([
            'calendar_id' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            $calendarListEntry = new Google_Service_Calendar_CalendarListEntry();
            $calendarListEntry->setId($calendar->google_id);
            $createdCalendarListEntry = $service->calendarList->insert($calendarListEntry);

            // Sync calendars
            Auth::user()->jobs_status = 'started';
            Auth::user()->save();
            $this->dispatch(new SyncCalendars(Auth::user()));

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Subscribe success',
                    'calendar' => $createdCalendarListEntry
                ]
            ]);
        } catch(\Exception $ex) {

            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar not found or not have public access'
                    ]
                ]);
            } else if ($ex->getErrors()[0]['message']) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => $ex->getErrors()[0]['message']
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 0,
                ]);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unsubscribeCalendarAction(Request  $request): JsonResponse
    {
        $request->validate([
            'calendar_id' => 'required'
        ]);


        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            $service->calendarList->delete($calendar->google_id);

            // Delete user calendar from DB
            //$calendar->delete();

            $calendar->where(['user_id' => Auth::user()->id, 'google_id' => $calendar->google_id])->delete();

            Auth::user()->jobs_status = 'started';
            Auth::user()->save();
            $this->dispatch(new SyncCalendars(Auth::user()));

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Unsubscribe success',
                ]
            ]);
        } catch(\Exception $ex) {
            if ($ex->getCode() === 401) {
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar not found or not have public access'
                    ]
                ]);
            } else if ($ex->getErrors()[0]['message']) {
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => $ex->getErrors()[0]['message']
                    ]
                ]);
            } else {
                return response()->json([
                    'code' => 0
                ]);
            }
        }
    }

    /**
     * @param $dateTime
     * @return Carbon
     */
    protected function parseDatetime($dateTime): Carbon
    {
        $rawDatetime = $dateTime->dateTime ?: $dateTime->date;
        return Carbon::parse($rawDatetime)->setTimezone(config('app.timezone'));
    }

    private function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

}
