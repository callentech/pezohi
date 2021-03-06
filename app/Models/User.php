<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Concerns\Synchronizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Jobs\SynchronizeGoogleCalendars;
use App\Jobs\WatchGoogleCalendars;

use DB;
use Auth;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use Synchronizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_access_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // public static function boot()
    // {
    //     parent::boot();
    //     // static::created(function ($googleAccount) {
    //     //     //SynchronizeGoogleCalendars::dispatch($googleAccount);
    //     // });
    // }

    public function synchronize()
    {
        //SynchronizeGoogleCalendars::dispatch($this);
    }

    public function synchronizeCalendars()
    {
        SynchronizeGoogleCalendars::dispatch($this);
    }

    public function watch()
    {
        //WatchGoogleCalendars::dispatch($this);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    
    
}
