@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            
 
            <all-calendars-component :data="{{ $calendars }}" new_event_form_action="{{ route('addCalendarEvent') }}" csrf_token="{{ csrf_token() }}"></all-calendars-component>


        </div>
    </div>
</div>

<add-edit-calendar-modal-component></add-edit-calendar-modal-component>

@endsection
