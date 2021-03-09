<?php

namespace App\Jobs;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use App\Services\Google;
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

                $this->user->calendars()->updateOrCreate(
                    [
                        'google_id' => $googleCalendar->id,
                    ],
                    [
                        'access_role' => 'owner',
                        'name' => $googleCalendar->summary,
                        'description' => $googleCalendar->description,
                        'color' => $googleCalendar->backgroundColor,
                        'timezone' => $googleCalendar->timeZone
                    ]
                );

                // Sync Calendar Events
                $calendar = Calendar::where('google_id', $googleCalendar->id)->first();
                $eventsPageToken = NULL;
                do {
                    $optParams = ['pageToken' => $eventsPageToken];
                    $eventsList = $service->events->listEvents($googleCalendar->id, $optParams);
                    foreach ($eventsList as $googleEvent) {

                        // Delete Event
                        if ($googleEvent->status === 'cancelled') {
                            Event::where('google_id', $googleEvent->id)->delete();
                            continue;
                        }

                        var_dump($googleEvent->location);
                        echo "\n";

                        $calendar->events()->updateOrCreate(
                            [
                                'google_id' => $googleEvent->id
                            ],
                            [
                                'google_id' => $googleEvent->id,
                                'name' => $googleEvent->summary,
                                'description' => $googleEvent->description,
                                'location' => $googleEvent->location,
                                'allday' => 0,
                                'started_at' => $this->parseDatetime($googleEvent->start),
                                'ended_at' => $this->parseDatetime($googleEvent->end)
                            ]
                        );
                    }
                    $eventsPageToken = $eventsList->getNextPageToken();
                } while ($eventsPageToken);
            }
            $this->pageToken = $calendarsList->getNextPageToken();
        } while($this->pageToken);

        $this->user->jobs_status = 'finished';
        $this->user->save();

    }

    /**
     * @param $googleDatetime
     * @return Carbon
     */
    protected function parseDatetime($googleDatetime): Carbon
    {
        $rawDatetime = $googleDatetime->dateTime ?: $googleDatetime->date;
        return Carbon::parse($rawDatetime)->setTimezone('UTC');
    }
}
