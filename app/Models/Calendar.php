<?php

namespace App\Models;

use App\Concerns\Synchronizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Jobs\SynchronizeGoogleEvents;
use App\Jobs\WatchGoogleEvents;

class Calendar extends Model
{
	use Synchronizable;

    protected $fillable = ['google_id', 'access_role', 'name', 'color', 'timezone'];

    public function googleAccount()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

   	public function synchronize()
    {
        SynchronizeGoogleEvents::dispatch($this);
    }

    public function watch()
    {
        //WatchGoogleEvents::dispatch($this);
    }
}
