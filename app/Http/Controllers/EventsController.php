<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Services\Google;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
{
    public function addSingleEventAction(Request $request)
    {
        $request->validate([
            'calendar_id' => 'required',
            'event-start-date' => 'required',
            'event-start-time-hours' => 'required',
            'event-start-time-minutes' => 'required',
            'event-start-time-ampm' => 'required',
            'event-end-date' => 'required',
            'event-end-time-hours' => 'required',
            'event-end-time-minutes' => 'required',
            'event-end-time-ampm' => 'required',
            'event_location' => 'required',
            'event_type' => 'required',
            'event_description' => 'required',
            'event_started_at' => 'required',
            'event_ended_at' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {

            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            // Save new event data to Google Calendar
            $started_at = date_create($request->event_started_at);
            $ended_at = date_create($request->event_ended_at);

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => 'Pezohi Event',
                'location' => $request->event_location,
                'description' => $request->event_description,
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
            $extendedProperties->setPrivate(['type' => $request->event_type]);
            $newGoogleEvent->setExtendedProperties($extendedProperties);
            $newGoogleEvent = $service->events->insert($calendar->google_id, $newGoogleEvent);

            // Save new event data to DB
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
            $calendar->touch();

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Event created successfully'
                ]
            ]);

        } catch (\Exception $ex) {
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
    public function editSingleEventAction(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'startDate' => 'required',
            'startTimeHours' => 'required',
            'startTimeMinutes' => 'required',
            'startTimeAmPm' => 'required',
            'endDate' => 'required',
            'endTimeHours' => 'required',
            'endTimeMinutes' => 'required',
            'endTimeAmPm' => 'required',
            'location' => 'required',
            'type' => 'required',
            'description' => 'required'
        ]);

        $event = Event::find($request->id);
        if (!$event) {
            return response()->json([
                'code' => 404,
                'data' => [
                    'message' => 'Event not found'
                ]
            ]);
        }

        $startedHours = $request->startTimeAmPm == 'AM' ? $request->startTimeHours : $request->startTimeHours + 12;
        $started_at = new Carbon($request->startDate.' '.$startedHours.':'.$request->startTimeMinutes);

        $endedHours = $request->endTimeAmPm == 'AM' ? $request->endTimeHours : $request->endTimeHours + 12;
        $ended_at = new Carbon($request->endDate.' '.$endedHours.':'.$request->endTimeMinutes);

//        $started_at = date_create($request->dateTime['startDate']);
//        $ended_at = date_create($request->dateTime['endDate']);

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $updatedEvent = $service->events->get($event->calendar->google_id, $event->google_id);

            $updatedEvent->start = [
                'dateTime' => date_format($started_at, 'c'),
                'timeZone' => config('app.timezone')
            ];
            $updatedEvent->end = [
                'dateTime' => date_format($ended_at, 'c'),
                'timeZone' => config('app.timezone')
            ];

            $updatedEvent->location = trim($request->location);
            $updatedEvent->description = trim($request->description);

            $extendedProperties = new Google_Service_Calendar_EventExtendedProperties();
            $extendedProperties->setPrivate(['type' => $request->type]);
            $updatedEvent->setExtendedProperties($extendedProperties);

            $updatedEvent = $service->events->update($event->calendar->google_id, $updatedEvent->getId(), $updatedEvent);

            // Update event data in DB
            $event->name = $updatedEvent->summary;
            $event->type = $updatedEvent->extendedProperties->getPrivate()['type'];
            $event->description =$updatedEvent->description;
            $event->location = $updatedEvent->location;
            $event->started_at = $this->parseDatetime($updatedEvent->start);
            $event->ended_at = $this->parseDatetime($updatedEvent->end);
            $event->updated_data_at = Carbon::parse($updatedEvent->updated)->setTimezone($updatedEvent->start->timeZone);
            $event->save();

            // Update event calendar
            $event->calendar->touch();

            return response()->json([
                'code' => 1,
                'data' => [
//                    'started_at' => Carbon::parse($updatedEvent->start->dateTime)->setTimezone($updatedEvent->start->timeZone),
//                    'ended_at' => Carbon::parse($updatedEvent->end->dateTime)->setTimezone($updatedEvent->end->timeZone),
                    'event' => $event,
                    'message' => 'Event updated successfully'
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

        exit;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteEventAction(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required'
        ]);

        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json([
                'code' => 404,
                'data' => [
                    'message' => 'Event not found'
                ]
            ]);
        }

        try {

//            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
//            $service->events->delete($event->calendar->google_id, $event->google_id);
//
//            // Delete event from DB
//            $event->calendar->touch();
//            $event->delete();

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Google Event delete success',
                    'calendarId' => $event->calendar->id
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
        /*
         * $request->validate([
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
         *
         *
         *
         *
         * */
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
