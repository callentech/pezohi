<template>



    <div class="content-wrapper">

        <div class="page-head">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1>Calendars</h1>
                        <p class="">You have {{ calendars.length }} calendars</p>
                    </div>
                    <div class="col-lg-6 text-lg-right">
                        <button type="button" class="btn btn-primary" @click="showAddCalendarModal"><i class="fa fa-plus"></i>Add Calendar</button>
                    </div>
                </div>
        </div>

        <div class="filter-links">
            <button v-for="typeFilter in calendarsTypesFilters" :key="typeFilter.title" @click="applyCalendarsTypeFilter(typeFilter)" type="button" class="btn btn-sm" :class="{ 'active': typeFilter.active }">{{ typeFilter.title }}</button>
        </div>

        <div class="filter-content">



            

                    <div v-for="typeFilter in calendarsTypesFilters" class="item" :class="{ 'active': typeFilter.active }" :data-id="typeFilter.val">

                        <div class="calendarsDataSorting">
                            <div class="row">
                                <div class="col-3">
                                    <a class="sort-link" href="#">Name <i class="fas fa-arrow-down"></i></a>
                                </div>
                                <div class="col-1">
                                    <a class="sort-link" href="#">Events <i class="fas fa-arrow-down"></i></a>
                                </div>
                                <div class="col-3">
                                    <a class="sort-link" href="#">Owner <i class="fas fa-arrow-down"></i></a>
                                </div>
                                <div class="col-3">
                                    <a class="sort-link" href="#">Last Updated <i class="fas fa-arrow-down"></i></a>
                                </div>
                                <div class="col-2">

                                </div>
                            </div>
                        </div>

                        <div class="calendars-list">
                            
                            <div v-for="calendar in calendars" class="card calendar-single">


                                
                                <div class="card-heading">
                                    <div class="row align-items-center">
                                        <div class="col-3">
                                            {{calendar.summary}}
                                        </div>
                                        <div class="col-1">
                                            {{ calendar.events.length }}
                                        </div>
                                        <div class="col-3">
                                            <span v-if="calendar.accessRole == 'owner'">
                                                me
                                            </span>
                                            <span v-else>
                                                other
                                            </span>
                                        </div>
                                        <div class="col-1">
                                            <!--9 hours ago-->
                                            {{ calendar.lastUpdated }}
                                        </div>
                                        <div class="col-4 text-right">
                                            <!-- <button type="button" class="btn btn-primary" name="button"><i class="fa fa-user-o"></i>Share</button>
                                            <button type="button" class="btn btn-primary" name="button"><i class="fa fa-bell-o"></i>Subscribe</button> -->
                                            <button type="button" class="btn btn-light pull-right" name="button"><i class="fas fa-angle-down"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <!--
                                <div class="card-body">


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
                            -->
                            </div>

                        </div>


                    </div>

                
            
        </div>

    </div>

</template>

<script>

    export default {

        props:['data'],

        data() {
            return {

                calendars: this.data,
                calendarsTypesFilters: [
                    { title: 'All calendars', val: 'all', active: true }, 
                    { title: 'Owned by me', val: 'owned', active: false},
                    { title: 'Shared with me', val: 'shared', active: false}
                ]
            }
        },

        methods: {
            applyCalendarsTypeFilter: function(typeFilter) {

                this.calendarsTypesFilters.forEach(typeFilter => {
                    typeFilter.active = false;
                });

                typeFilter.active = true;
            },

            showAddCalendarModal: function() {
                jQuery('#addCalendarModal').modal('show');
            }
        },

        mounted() {

        }
    }
</script>
