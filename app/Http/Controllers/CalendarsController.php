<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCalendars;
use App\Models\Calendar;
use App\Models\Event;
use App\Services\Google;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;


class CalendarsController extends Controller
{
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
            } else {
                return response()->json([
                    'code' => 0
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
                'code' => 0
            ]);
        }

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
            } else {
                return response()->json([
                    'code' => 0,
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

}
