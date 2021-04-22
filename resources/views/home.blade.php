@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <all-calendars-component :data="{{ $calendars }}" jobs_status="{{ $jobs_status }}" user_id="{{Auth::user()->id}}" user_role="{{ Auth::user()->role }}"></all-calendars-component>
        </div>
    </div>
</div>
<add-edit-calendar-modal-component></add-edit-calendar-modal-component>
@endsection
