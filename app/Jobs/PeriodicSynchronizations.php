<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Synchronization;

class PeriodicSynchronizations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        //Synchronization::get()->each->ping();

        echo "\n START sync \n";
        $syncs = Synchronization::get();
        foreach ($syncs as $sync) {
            if ($sync->synchronizable_type == 'App\Models\User') {
                $sync->synchronizable->synchronizeCalendars();
            } else {
                $sync->ping();
            }
        }
        echo "\n END Sync \n";
       
    }
}
