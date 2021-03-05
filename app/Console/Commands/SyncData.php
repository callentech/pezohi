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
        // echo "\nSTART\n";
        // Synchronization::get()->each->ping();
        // echo "\nEND\n";

        $sync = new PeriodicSynchronizations();
        var_dump($sync);
        // return 0;
    }
}
