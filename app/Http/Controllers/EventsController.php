<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Event;
use App\Services\Google;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
{
    public function addSingleEventAction(Request $request)
    {
        $request->validate([
            'calendar_id' => 'required',
            'new_event_datetime' => 'required',
            'new_event_address' => 'required',
            'new_event_type' => 'required',
            'new_event_notes' => 'required'
        ]);

        $calendar = Calendar::with('events')->find($request->calendar_id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {

            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            $started_at = date_create($request->new_event_datetime);
            $ended_at = date_create($request->new_event_datetime);

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => 'Pezohi Event',
                'location' => $request->new_event_address,
                'description' => $request->new_event_notes,
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
            $extendedProperties->setPrivate(['type' => $request->new_event_type]);
            $newGoogleEvent->setExtendedProperties($extendedProperties);
            $newGoogleEvent = $service->events->insert($calendar->google_id, $newGoogleEvent);

            // Save new event data to DB
            $newEvent = new Event([
                'google_id' => $newGoogleEvent->id,
                'name' => $newGoogleEvent->summary,
                'type' => $newGoogleEvent->extendedProperties->getPrivate()['type'],
                'description' => $newGoogleEvent->description,
                'location' => $newGoogleEvent->location,
                'allday' => 0,
                'started_at' => $this->parseDatetime($newGoogleEvent->start),
                'ended_at' => $this->parseDatetime($newGoogleEvent->end)
            ]);
            $calendar->events()->save($newEvent);

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Calendar updated successfully'
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

    public function editSingleEventAction(Request $request)
    {
        /**
         * array(5) {
         * ["id"]=> int(5)
         * ["dateTime"]=> string(9) "3/11/2021"
         * ["location"]=> string(10) "Location 2"
         * ["type"]=> string(4) "game"
         * ["description"]=> string(13) "Description 2" }
         */

        $request->validate([
            'id' => 'required',
            'dateTime' => 'required',
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

        $started_at = date_create($request->dateTime);
        $ended_at = date_create($request->dateTime);

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
            $event->save();

            return response()->json([
                'code' => 1,
                'data' => [
                    'updatedEvent' => $updatedEvent,
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

    protected function parseDatetime($dateTime): Carbon
    {
        $rawDatetime = $dateTime->dateTime ?: $dateTime->date;
        return Carbon::parse($rawDatetime)->setTimezone(config('app.timezone'));
    }

}
