<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $with = ['calendar'];
    protected $fillable = ['google_id', 'name', 'type', 'description', 'location', 'status', 'allday', 'started_at', 'ended_at', 'updated_data_at'];

    /**
     * @return BelongsTo
     */
    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function attendee()
    {
        return $this->hasMany(Attendee::class);
    }
}
