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

    protected function parseDatetime($dateTime): Carbon
    {
        $rawDatetime = $dateTime->dateTime ?: $dateTime->date;
        return Carbon::parse($rawDatetime)->setTimezone(config('app.timezone'));
    }

}
