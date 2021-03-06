<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Synchronization;
use App\Jobs\PeriodicSynchronizations;

class SyncData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sync User's Calendars Data";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo "\n START Sync \n";

        $syncs = Synchronization::get();
        foreach ($syncs as $sync) {
            if ($sync->synchronizable_type == 'App\Models\User') {
                $sync->synchronizable->synchronizeCalendars();
            } else {
                $sync->ping();
            }
        }
        echo "\n END Sync \n";

        //$sync = new PeriodicSynchronizations();
        
        // return 0;
    }
}
