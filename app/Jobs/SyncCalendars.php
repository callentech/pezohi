<?php

namespace App\Jobs;

use App\Models\Calendar;
use App\Models\User;
use App\Services\Google;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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

    public function handle()
    {
        $access_token = $this->user->google_access_token;
        $pageToken = $this->pageToken;
        $tokens = compact('access_token');
        $options = compact('pageToken');
        $service = app(Google::class)->connectUsing($tokens)->service('Calendar');

        do {
            $calendarsList = $service->calendarList->listCalendarList($options);

            // Delete calendars
            $userCalendars = Calendar::where('user_id', $this->user->id)->get();
            foreach ($userCalendars as $userCalendar) {
                $isDeleted = TRUE;
                foreach ($calendarsList as $googleCalendar) {
                    if ($googleCalendar->id == $userCalendar->google_id) {
                        $isDeleted = FALSE;
                    }
                }
                if ($isDeleted) {
                    echo 'Calendar '.$userCalendar->google_id." is Deleted\n";
                    Calendar::destroy($userCalendar->id);
                }
            }

            // Sync calendars
            foreach ($calendarsList as $googleCalendar) {

                if (Str::contains($googleCalendar->id, ['#holiday@group.v.calendar.google.com'])
                    || Str::contains($googleCalendar->id, ['addressbook#contacts@group.v.calendar.google.com'])
                    || $googleCalendar->id == $this->user->email) {
                    continue;
                }

                // Delete calendar
                if ($googleCalendar->deleted) {
                    Calendar::where('google_id', $googleCalendar->id)->delete();
                }

                $existCalendar = Calendar::where('google_id', $googleCalendar->id)->first();
                if ($existCalendar) {
                    echo "Update calendar: " . $googleCalendar->summary . "\n\n";
                    $existCalendar->access_role = $googleCalendar->accessRole;
                    $existCalendar->name = $googleCalendar->summary;
                    $existCalendar->color = $googleCalendar->backgroundColor;
                    $existCalendar->timezone = $googleCalendar->timeZone;
                    $existCalendar->save();
                } else {
                    echo "Insert calendar: " . $googleCalendar->summary . "\n\n";
                    $createdCalendar = new Calendar([
                        'google_id' => $googleCalendar->id,
                        'access_role' => 'owner',
                        'name' => $googleCalendar->summary,
                        'color' => $googleCalendar->backgroundColor,
                        'timezone' => $googleCalendar->timeZone,
                    ]);
                    $this->user->calendars()->save($createdCalendar);
                }
            }

            $this->pageToken = $calendarsList->getNextPageToken();
        } while($this->pageToken);
    }
}
