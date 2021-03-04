

@foreach ($calendars as $calendar)

{{ $calendar->name }}<br>
{{ $calendar->google_id }}<br>
	
	@foreach ($calendar->events as $event)

		{{ $event->name }} - <span
                                    class="badge badge-pill" 
                                    style="background-color: {{ $event->calendar->color }};"
                                >
                                    {{ $event->calendar->name }}
                                </span>

	@endforeach
<!-- 
<br>
<br>

@foreach ($calendar->events as $event)
	
	{{ $event->name }}
	<br>

@endforeach
<hr>
<hr>
 -->

 <hr>


@endforeach