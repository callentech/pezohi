@extends('layouts.admin_app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-11">

                <transition name="fade">
                <admin-calendars-component v-if="showAdminCalendars" :data="{{ $calendars }}"></admin-calendars-component>

                <admin-users-component v-if="showAdminUsers" :data="{{ $users }}"></admin-users-component>
                </transition>
            </div>
        </div>
    </div>
    <add-edit-calendar-modal-component></add-edit-calendar-modal-component>
@endsection
