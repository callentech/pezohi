@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            
 
            <all-calendars-component :data="{{ $calendars }}" new_event_form_action="{{ route('addNewCalendarEvent') }}" csrf_token="{{ csrf_token() }}"></all-calendars-component>

            
            

           

            <!--
            <tabs>
                <tab name="All Calendars" :selected="true">
                    <div class="filters">
                        <div class="row">
                            <div class="col-3">
                                <a href="#">Name</a>
                            </div>
                            <div class="col-1">
                                <a href="#">Events</a>
                            </div>
                            <div class="col-3">
                                <a href="#">Owner</a>
                            </div>
                            <div class="col-1">
                                <a href="#">Updated</a>
                            </div>
                            <div class="col-4">

                            </div>
                        </div>
                    </div>
                    <div class="calendars-list">
                        <calendar class="card calendar-single">
                            <div class="card-heading">
                                <div class="row align-items-center">
                                    <div class="col-3">
                                        Spring 2021
                                    </div>
                                    <div class="col-1">
                                        8
                                    </div>
                                    <div class="col-3">
                                        mochaella.prinston1974@gmail.com
                                    </div>
                                    <div class="col-1">
                                        9 hours ago
                                    </div>
                                    <div class="col-4">
                                        <button type="button" class="btn btn-primary" name="button"><i class="fa fa-user-o"></i>Share</button>
                                        <button type="button" class="btn btn-primary" name="button"><i class="fa fa-bell-o"></i>Subscribe</button>
                                        <button type="button" class="btn btn-light pull-right" name="button" v-on:click="seen = !seen"><i class="fa fa-angle-down"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" v-if="seen">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        1-5 of 12 <i class="fa fa-angle-right"></i> <a href="#" class="btn btn-link">View all</a>
                                    </div>
                                    <div class="col-lg-6 text-right">
                                        <button type="button"class="btn btn-primary pull-right" name="button"><i class="fa fa-plus"></i> Add event</button>
                                    </div>
                                </div>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <a href="#">Date</a>
                                            </th>
                                            <th>
                                                <a href="#">Time</a>
                                            </th>
                                            <th>
                                                <a href="#">Address</a>
                                            </th>
                                            <th>
                                                <a href="#">Event</a>
                                            </th>
                                            <th>
                                                <a href="#">Notes</a>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>12/04/21</td>
                                            <td>5:20 PM - 7:20 PM</td>
                                            <td>290 N. Hilldale</td>
                                            <td>Game</td>
                                            <td>Take snacks for afterparty</td>
                                            <td class="text-right">
                                                <a href="#" class="btn btn-light"><i class="fa fa-edit"></i></a>
                                                <a href="#" class="btn btn-light"><i class="fa fa-ellipsis-v "></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </calendar>
                    </div>
                </tab>
                <tab name="Owned by me">
                    <h1>How much we do it for</h1>
                </tab>
                <tab name="Shared with me">
                    <h1>Why we do it</h1>
                </tab>
            </tabs>
        -->
        </div>
    </div>
</div>

<add-calendar-component  form_action="{{ route('addNewCalendar') }}" csrf_token="{{ csrf_token() }}"></add-calendar-component>

@endsection
