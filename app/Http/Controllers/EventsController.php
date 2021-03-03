<?php

namespace App\Http\Controllers;

class EventsController extends Controller
{
	protected $client;
    protected $googleClientToken;

    public function __construct()
    {
        $this->middleware('auth');

        $this->client = new Google_Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setDeveloperKey(env('GOOGLE_API_KEY'));

        //$this->client->setAccessType('offline');
    }

    public function insert($event, $calendar)
    {
    	
    }

    public function import($event, $calendar)
    {
    	/*

			$event = new Google_Service_Calendar_Event();
			$event->setSummary('Appointment');
			$event->setLocation('Somewhere');
			$start = new Google_Service_Calendar_EventDateTime();
			$start->setDateTime('2011-06-03T10:00:00.000-07:00');
			$event->setStart($start);
			$end = new Google_Service_Calendar_EventDateTime();
			$end->setDateTime('2011-06-03T10:25:00.000-07:00');
			$event->setEnd($end);
			$attendee1 = new Google_Service_Calendar_EventAttendee();
			$attendee1->setEmail('attendeeEmail');
			// ...
			$attendees = array($attendee1,
			                   // ...,
			                  );
			$event->attendees = $attendees;
			$organizer = new Google_Service_Calendar_EventOrganizer();
			$organizer->setEmail('organizerEmail');
			$organizer->setDisplayName('organizerDisplayName');
			$event->setOrganizer($organizer);
			$event->setICalUID('originalUID');
			$importedEvent = $service->events->import('primary', $event);




    	*/
    }
}