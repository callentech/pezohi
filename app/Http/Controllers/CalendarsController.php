<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCalendars;
use App\Mail\EventStatusNotify;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Subscribe;
use App\Models\User;
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
            $subscribes = Subscribe::where('calendar_id', $calendar->id)->get();
            foreach ($subscribes as $subscribe) {
                $user = User::find($subscribe->user_id);
                Mail::to($user->email)->send(new EventStatusNotify($params));
            }
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
                return response()->json([
                    'code' => 0,
                    'data' => [
                        'message' => 'API Request Error'
                    ]
                ]);
            }
        }
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
        if (!Auth::user()) {
    		return response()->json([
                'code' => 401
            ]);
    	}

        $request->validate([
            'calendar_id' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0,
                'data' => [
                    'message' => 'Calendar not found',
                ]
            ]);
        }

        $subscribe = new Subscribe();
        $subscribe->calendar_id = $calendar->id;
        $subscribe->user_id = Auth::user()->id;
        $subscribe->save();

        return response()->json([
            'code' => 1,
            'data' => [
                'message' => 'Subscribe success',
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unsubscribeCalendarAction(Request  $request): JsonResponse
    {
    	if (!Auth::user()) {
    		return response()->json([
                'code' => 401
            ]);
    	}
    	
        $request->validate([
            'calendar_id' => 'required'
        ]);


        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0,
                'data' => [
                    'message' => 'Calendar not found',
                ]
            ]);
        }

        $subscribe = Subscribe::where(['user_id' => Auth::user()->id, 'calendar_id' => $calendar->id])->first();
        if (!$subscribe) {
            return response()->json([
                'code' => 0,
                'data' => [
                    'message' => 'Subscribe not found',
                ]
            ]);
        }

        $subscribe->delete();

        return response()->json([
            'code' => 1,
            'data' => [
                'message' => 'Unsubscribe success',
            ]
        ]);
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
