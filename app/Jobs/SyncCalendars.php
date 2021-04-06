<?php

namespace App\Jobs;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use App\Services\Google;
use Google_Service_Calendar_EventExtendedProperties;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SyncCalendars implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $pageToken;
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->pageToken = NULL;
    }

    /**
     *  Handle job method
     */
    public function handle()
    {
        $this->user->jobs_status = 'started';
        $this->user->save();

        $access_token = $this->user->google_access_token;
        $pageToken = $this->pageToken;
        $tokens = compact('access_token');
        $options = compact('pageToken');
        $service = app(Google::class)->connectUsing($tokens)->service('Calendar');



        do {
            $calendarsList = $service->calendarList->listCalendarList($options);

            // Remove deleted calendar from DB
            foreach ($this->user->calendars as $userCalendar) {
                $deletedCalendar = TRUE;
                foreach ($calendarsList as $googleCalendar) {
                    if ($googleCalendar->id == $userCalendar->google_id) {
                        $deletedCalendar = FALSE;
                    }
                }
                if ($deletedCalendar)  {
                    $deletedUserCalendar = Calendar::find($userCalendar->id);
                    if (!$deletedUserCalendar) {
                        continue;
                    }
                    $deletedUserCalendar->delete();
                }
            }

            // Sync calendars
            foreach ($calendarsList as $googleCalendar) {

                // Get only pezohi calendars
                $description = explode('|', $googleCalendar->description);
                if (!isset($description[0]) || trim($description[0]) != '#pezohi') {
                    continue;
                }

                // Delete calendar
                if ($googleCalendar->deleted) {
                    Calendar::where('google_id', $googleCalendar->id)->delete();
                    continue;
                }

                var_dump($googleCalendar);
                var_dump($googleCalendar->accessRole);
                echo "\n=========================================\n";

                $this->user->calendars()->updateOrCreate(
                    [
                        'google_id' => $googleCalendar->id,
                    ],
                    [
                        'access_role' => $googleCalendar->accessRole,
                        'name' => $googleCalendar->summary,
                        'description' => $googleCalendar->description,
                        'color' => $googleCalendar->backgroundColor,
                        'timezone' => $googleCalendar->timeZone,
                        'timestamps' => false
                    ]
                );

                // Sync Calendar Events
                $calendar = Calendar::where('google_id', $googleCalendar->id)->first();
                if (!$calendar) {
                    continue;
                }
                $eventsPageToken = NULL;
                do {
                    $optParams = ['pageToken' => $eventsPageToken];
                    $eventsList = $service->events->listEvents($googleCalendar->id, $optParams);
                    foreach ($eventsList as $googleEvent) {

                        $calendar->events()->updateOrCreate(
                            [
                                'google_id' => $googleEvent->id
                            ],
                            [
                                'google_id' => $googleEvent->id,
                                'name' => $googleEvent->summary,
                                'type' => $googleEvent->extendedProperties->getPrivate()['type'],
                                'description' => $googleEvent->description,
                                'location' => $googleEvent->location,
                                'status' => $googleEvent->status,
                                'allday' => 0,
                                'started_at' => Carbon::parse($googleEvent->start->dateTime)->setTimezone($googleEvent->start->timeZone),
                                'ended_at' => Carbon::parse($googleEvent->end->dateTime)->setTimezone($googleEvent->end->timeZone),
                                'updated_data_at' => Carbon::parse($googleEvent->updated)->setTimezone($googleEvent->start->timeZone)
                            ]
                        );



                    }
                    $eventsPageToken = $eventsList->getNextPageToken();
                } while($eventsPageToken);
            }
            $this->pageToken = $calendarsList->getNextPageToken();
        } while($this->pageToken);

        $this->user->jobs_status = 'finished';
        $this->user->save();
    }
}
