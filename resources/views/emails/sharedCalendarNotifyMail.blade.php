@component('mail::message')
# Calendar Was Shared for You

<div>
    Calendar URL: <a href="{{ $url }}"> {{ $url }}</a>
</div>

<div>
    DateTime: {{ $dateTime }}
</div>
@component('mail::button', ['url' => url('/login')])
    Sign In to {{ config('app.name') }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
