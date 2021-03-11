<template>

    <div class="content-wrapper">

        <div class="page-head">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <h1>Calendars</h1>
                    <p class="">You have {{ calendars.length }} calendars</p>
                </div>

                <div class="col-lg-8">
                    <div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ requestDanger }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ requestSuccess }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <div class="col-lg-2 text-lg-right">
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
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListBySummary">
                                Name
                                <i v-if="sortBySummaryDirection == 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByEvents">
                                Events
                                <i v-if="sortByEventsDirection == 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByOwner">
                                Owner
                                <i v-if="sortByOwnerDirection == 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByUpdated">
                                Updated
                                <i v-if="sortByUpdatedDirection == 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-4">

                        </div>
                    </div>
                </div>

                <div v-if="Object.keys(calendars).length > 0" class="calendars-list">
                    <div v-for="calendar in sortedCalendars">
                        <calendars-list-item-component :calendar="calendar" :jobs_status="jobs_status" ref='calendar'></calendars-list-item-component>
                    </div>
                </div>
                <div v-else class="calendars-list">
                    <div class="alert alert-info" role="alert">
                        Calendars not found ...
                    </div>
                </div>

            </div>
        </div>

        <div class="modal fade" id="confirmCalendarDelete" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="confirmCalendarDeleteLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-white bg-danger">
                        <h5 class="modal-title" id="confirmCalendarDeleteLabel">Delete calendar</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to delete calendar, this procedure is irreversible.</p>
                        <p>Do you want to proceed?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" :disabled="requestProcess">Cancel</button>

                        <button v-if="!requestProcess" type="button" class="btn btn-danger" @click="deleteCalendar(delete_calendar_id)">Delete</button>

                        <button v-else class="btn btn-danger" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    export default {
        props:['data', 'jobs_status'],

        data() {
            return {
                calendars: this.data,
                sortedCalendars: [],
                calendarsTypesFilters: [
                    { title: 'All calendars', val: 'all', active: true },
                    { title: 'Owned by me', val: 'owned', active: false},
                    { title: 'Shared with me', val: 'shared', active: false}
                ],

                requestDanger: false,
                requestSuccess: false,
                requestProcess: false,

                delete_calendar_id: '',

                sortBySummaryDirection: 'asc',
                sortByEventsDirection: 'asc',
                sortByOwnerDirection: 'asc',
                sortByUpdatedDirection: 'asc'
            }
        },

        created() {
            this.$root.$refs.allCalendars = this;
        },

        methods: {
            applyCalendarsTypeFilter: function(typeFilter) {

                this.calendarsTypesFilters.forEach(typeFilter => {
                    typeFilter.active = false;
                });

                typeFilter.active = true;
            },

            showAddCalendarModal: function() {
                this.$root.$refs.addEditCalendarModal.showAddCalendarModalAction();
            },

            showConfirmCalendarDelete: function(id) {
                this.delete_calendar_id = id;
                jQuery('#confirmCalendarDelete').modal('show');
            },

            deleteCalendar: function(id) {

                let currentObj = this;

                axios.interceptors.request.use(function (config) {
                    // Do something before request is sent
                    currentObj.requestProcess = true;
                    return config;
                }, function (error) {
                    // Do something with request error
                    return Promise.reject(error);
                });

                axios.post('/calendar-delete', { calendar_id: id })
                .then(function (response) {
                    if (response.data.code == 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        currentObj.requestDanger = 'Error Request';
                    }
                })
                .catch(function (error) {
                    console.log(error);
                    currentObj.requestDanger = 'Error Request';
                })
                .then(function() {
                    currentObj.requestProcess = false;
                    jQuery('#confirmCalendarDelete').modal('hide');
                });
            },

            sortArray: function(array, field, direction) {
                return _.orderBy(array, field, direction);
            },

            // Sort calendar list methods
            sortCalendarsListBySummary: function() {
                let direction = this.sortBySummaryDirection == 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'name', direction);
                this.sortBySummaryDirection = direction;
            },

            sortCalendarsListByEvents: function() {
                let direction = this.sortByEventsDirection == 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'eventsCount', direction);
                this.sortByEventsDirection = direction;
            },

            sortCalendarsListByOwner: function() {
                let direction = this.sortByOwnerDirection == 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'accessRole', direction);
                this.sortByOwnerDirection = direction;
            },

            sortCalendarsListByUpdated: function() {
                let direction = this.sortByUpdatedDirection == 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'updated_at', direction);
                this.sortByUpdatedDirection = direction;
            }
        },

        mounted() {
            this.sortCalendarsListByUpdated();


        }
    }
</script>
