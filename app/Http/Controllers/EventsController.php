<?php

namespace App\Http\Controllers;

use App\Mail\EventStatusNotify;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Subscribe;
use App\Models\User;
use App\Services\Google;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EventsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
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
    public function duplicateSingleEventAction(Request $request): JsonResponse
    {
        $request->validate([
            'duplicate_event_id' => 'required',
            'startDate' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
            'type' => 'required'
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

        $calendar = Calendar::with('events')->find($event->calendar->id);
        if (!$calendar) {
            return response()->json([
                'code' => 0
            ]);
        }

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            $startTimeArray = explode(' ', $request->startTime);
            $startTime = explode(':', $startTimeArray[0]);
            $startTimeAmPm = $startTimeArray[1];
            $startedHours = $startTimeAmPm == 'AM' ? $startTime[0] : $startTime[0] + 12;
            $started_at = new Carbon($request->startDate.' '.$startedHours.':'.$startTime[1]);

            $endTimeArray = explode(' ', $request->endTime);
            $endTime = explode(':', $endTimeArray[0]);
            $endTimeAmPm = $endTimeArray[1];
            $endedHours = $endTimeAmPm == 'AM' ? $endTime[0] : $endTime[0] + 12;
            $ended_at = new Carbon($request->startDate.' '.$endedHours.':'.$endTime[1]);

            if ($started_at > $ended_at) {
                return response()->json([
                    'code' => 0,
                    'data' => [
                        'message' => 'Datetime range is not correct'
                    ]
                ]);
            }

            $eventSummary = $calendar->name.' '.$request->type;

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => $eventSummary,
                'location' => isset($request->location) ? $request->location : '',
                'description' => isset($request->description) ? $request->description : '',
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
            $extendedProperties->setPrivate(['type' => $request->type]);
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
                    'event' => $newEvent,
                    'message' => 'Event duplicate successfully'
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
                        'message' => 'Request Error'
                    ]
                ]);
            }
        }

        /*
        $request->validate([
            'duplicate_event_id' => 'required',
            'startDate' => 'required',
            'startTimeHours' => 'required',
            'startTimeMinutes' => 'required',
            'startTimeAmPm' => 'required',
            'endDate' => 'required',
            'endTimeHours' => 'required',
            'endTimeMinutes' => 'required',
            'endTimeAmPm' => 'required',
            'type' => 'required'
        ]);

        

       

        try {
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            // Save new event data to Google Calendar
            $startedHours = $request->startTimeAmPm == 'AM' ? $request->startTimeHours : $request->startTimeHours + 12;
            $started_at = new Carbon($request->startDate.' '.$startedHours.':'.$request->startTimeMinutes);

            $endedHours = $request->endTimeAmPm == 'AM' ? $request->endTimeHours : $request->endTimeHours + 12;
            $ended_at = new Carbon($request->endDate.' '.$endedHours.':'.$request->endTimeMinutes);

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => 'Pezohi Event',
                'location' => isset($request->location) ? $request->location : '',
                'description' => isset($request->description) ? $request->description : '',
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
            $extendedProperties->setPrivate(['type' => $request->type]);
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
//                    'started_at' => Carbon::parse($updatedEvent->start->dateTime)->setTimezone($updatedEvent->start->timeZone),
//                    'ended_at' => Carbon::parse($updatedEvent->end->dateTime)->setTimezone($updatedEvent->end->timeZone),
                    'event' => $newEvent,
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
                    'data' => [
                        'message' => 'Request Error'
                    ]
                ]);
            }
        }

        */
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addSingleEventAction(Request $request): JsonResponse
    {

        $request->validate([
            'calendar_id' => 'required',
            'startDate' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
            'type' => 'required'
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

            $startTimeArray = explode(' ', $request->startTime);
            $startTime = explode(':', $startTimeArray[0]);
            $startTimeAmPm = $startTimeArray[1];
            $startedHours = $startTimeAmPm == 'AM' ? $startTime[0] : $startTime[0] + 12;
            $started_at = new Carbon($request->startDate.' '.$startedHours.':'.$startTime[1]);

            $endTimeArray = explode(' ', $request->endTime);
            $endTime = explode(':', $endTimeArray[0]);
            $endTimeAmPm = $endTimeArray[1];
            $endedHours = $endTimeAmPm == 'AM' ? $endTime[0] : $endTime[0] + 12;
            $ended_at = new Carbon($request->startDate.' '.$endedHours.':'.$endTime[1]);

            if ($started_at > $ended_at) {
                return response()->json([
                    'code' => 0,
                    'data' => [
                        'message' => 'Datetime range is not correct'
                    ]
                ]);
            }

            $eventSummary = $calendar->name.' '.$request->type;

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => $eventSummary,
                'location' => $request->location ?? '',
                'description' => $request->notes ?? '',
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
            $extendedProperties->setPrivate(['type' => $request->type]);
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
                    'data' => [
                        'message' => 'API Request Error'
                    ]
                ]);
            }
        }



        /*

        try {

            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');

            // Save new event data to Google Calendar
            $started_at = date_create($request->event_started_at);
            $ended_at = date_create($request->event_ended_at);

            $newGoogleEvent = new Google_Service_Calendar_Event(array(
                'summary' => 'Pezohi Event',
                'location' => isset($request->event_location) ? $request->event_location : '',
                'description' => isset($request->event_description) ? $request->event_description : '',
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
    public function editSingleEventAction(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required',
            'startDate' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
            'type' => 'required'
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

        $startTimeArray = explode(' ', $request->startTime);
        $startTime = explode(':', $startTimeArray[0]);
        $startTimeAmPm = $startTimeArray[1];
        $startedHours = $startTimeAmPm == 'AM' ? $startTime[0] : $startTime[0] + 12;
        $started_at = new Carbon($request->startDate.' '.$startedHours.':'.$startTime[1]);

        $endTimeArray = explode(' ', $request->endTime);
        $endTime = explode(':', $endTimeArray[0]);
        $endTimeAmPm = $endTimeArray[1];
        $endedHours = $endTimeAmPm == 'AM' ? $endTime[0] : $endTime[0] + 12;
        $ended_at = new Carbon($request->startDate.' '.$endedHours.':'.$endTime[1]);

        

        if ($started_at > $ended_at) {
            return response()->json([
                'code' => 0,
                'data' => [
                    'message' => 'Datetime range is not correct'
                ]
            ]);
        }

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

            $updatedEvent->location = $request->location;
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

            // Send Mail to Calendar Shared and Owned users
            $action = 'Updated';
            $this->sendMainNotify($event, $action);

            return response()->json([
                'code' => 1,
                'data' => [
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
                    'data' => [
                        'message' => 'Request Error'
                    ]
                ]);
            }
        }

        /*

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

            $updatedEvent->location = $request->location;
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

            // Send Mail to Calendar Shared and Owned users
            $action = 'Updated';
            $this->sendMainNotify($event, $action);

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
                    'data' => [
                        'message' => 'Request Error'
                    ]
                ]);
            }
        }

        */
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

           $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
           $service->events->delete($event->calendar->google_id, $event->google_id);

            // Send Mail to Calendar Shared and Owned users
            $action = 'Deleted';
            $this->sendMainNotify($event, $action);

            // Delete event from DB
            $event->calendar->touch();
            $event->delete();

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Google Event delete success',
                    'calendarId' => $event->calendar->id
                ]
            ]);
        } catch(\Exception $ex) {
            if ($ex->getCode() === 401) { //401 Unauthorized
                Auth::logout();
                return response()->json([
                    'code' => 401
                ]);
            } else if ($ex->getCode() === 404) { //404 Not Found 
                return response()->json([
                    'code' => 404,
                    'data' => [
                        'message' => 'Google calendar or event not found'
                    ]
                ]);
            } else if ($ex->getCode() === 410) { //410 Gone 

                // Delete event from DB
                $event->calendar->touch();
                $event->delete();
                return response()->json([
                    'code' => 1,
                    'data' => [
                        'message' => 'Google Event delete success',
                        'calendarId' => $event->calendar->id
                    ]
                ]);
                

            } else {

                var_dump($ex->getCode());
                exit;
                return response()->json([
                    'code' => 0,
                    'data' => [
                        'message' => $ex->getMessage()
                    ]
                ]);
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelEventAction(Request $request): JsonResponse
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
            $service = app(Google::class)->connectUsing(Auth::user()->google_access_token)->service('Calendar');
            $googleEvent = $service->events->get($event->calendar->google_id, $event->google_id);
            $googleEvent->setStatus('cancelled');
            $updatedEvent = $service->events->update($event->calendar->google_id, $googleEvent->getId(), $googleEvent);

            // Send Mail to Calendar Shared and Owned users
            $action = 'Cancelled';
            $this->sendMainNotify($event, $action);

            // Update event in DB
            $event->status = $updatedEvent->status;
            $event->save();
            $event->calendar->touch();

            return response()->json([
                'code' => 1,
                'data' => [
                    'message' => 'Google Event cancelled success',
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
