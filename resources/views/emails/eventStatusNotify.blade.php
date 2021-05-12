@component('mail::message')
# Event was changed
<div>
    Calendar: <a href="{{ url('/').'/calendar/'.$calendar->google_id }}"> {{ $calendar->name }}</a>
</div>
<hr>
<div>
    Event started: {{ \Carbon\Carbon::parse($event->started_at)->format('m/d/Y H:i:s') }}<br>
    Event ended: {{ \Carbon\Carbon::parse($event->ended_at)->format('m/d/Y H:i:s') }}<br>
    Event location: {{ $event->location }}<br>
    Event type: {{ $event->type }}<br>
    Event notes: {{ $event->description }}<br>
</div>
<hr>
<div>
    Action: {{ $action }}
</div>
<hr>
<div>
    DateTime: {{ $dateTime }}
</div>
@component('mail::button', ['url' => url('/login')])
Sign In to {{ config('app.name') }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

