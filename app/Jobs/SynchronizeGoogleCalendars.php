<?php

namespace App\Jobs;

use App\Jobs\SynchronizeGoogleResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Auth;


class SynchronizeGoogleCalendars extends SynchronizeGoogleResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function getGoogleRequest($service, $options)
    {
        return $service->calendarList->listCalendarList($options);
    }

    public function syncItem($googleCalendar)
    {
        if ($googleCalendar->deleted) {
            return $this->synchronizable->calendars()
                ->where('google_id', $googleCalendar->id)
                ->get()->each->delete();
        }

        // var_dump($googleCalendar);
        // exit;

        if (!Str::contains($googleCalendar->id, ['#holiday@group.v.calendar.google.com']) && !Str::contains($googleCalendar->id, ['addressbook#contacts@group.v.calendar.google.com'])) {

            $this->synchronizable->calendars()->updateOrCreate(
                [
                    'google_id' => $googleCalendar->id,
                ],
                [
                    'access_role' => $googleCalendar->accessRole,
                    'name' => $googleCalendar->summary,
                    'color' => $googleCalendar->backgroundColor,
                    'timezone' => $googleCalendar->timeZone,
                ]
            );
                 
        }
    }

    public function dropAllSyncedItems()    
    {
        // Here we use `each->delete()` to make sure model listeners are called.
        $this->synchronizable->calendars->each->delete();   
    }
}
