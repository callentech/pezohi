<template>

    <div class="content-wrapper">

        <div class="page-head">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <h1>Calendars</h1>
                    <p class="">
                        You have {{ calendars.length }} calendars
                        <span v-if="user_role === 'admin'">
                            | <a  href="/admin-home">Admin Dashboard</a>
                        </span>
                    </p>
                </div>
                <div class="col-lg-6">
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
                                <i v-if="sortBySummaryDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByEvents">
                                Events
                                <i v-if="sortByEventsDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByOwner">
                                Owner
                                <i v-if="sortByOwnerDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-2">
                            <a class="sort-link" href="javascript:void(0)" @click="sortCalendarsListByUpdated">
                                Updated
                                <i v-if="sortByUpdatedDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                            </a>
                        </div>
                        <div class="col-4">

                        </div>
                    </div>
                </div>

                <div v-if="Object.keys(calendars).length > 0" class="calendars-list">
                    <div v-for="calendar in sortedCalendars">
                        <calendars-list-item-component @toggle="filterCalendars" :calendar="calendar" :jobs_status="jobs_status" ref='calendar'></calendars-list-item-component>
                    </div>
                </div>
                <div v-else class="calendars-list">
                    <div class="alert alert-info" role="alert">
                        Calendars not found ...
                    </div>
                </div>

            </div>
        </div>

        <!-- Confirm Delete Calendar -->
        <div v-if="showConfirmDeleteCalendarModal">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-wrapper">
                        <div id="confirmDeleteCalendarModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header text-white bg-danger">
                                        <h5 class="modal-title w-100 text-center">Confirm delete calendar</h5>
                                        <button type="button" class="close" @click="hideConfirmCalendarDeleteModal">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <p>You are about to delete calendar, this procedure is irreversible.</p>
                                        <p>Do you want to proceed?</p>
                                    </div>

                                    <div class="modal-footer">
                                        <button v-if="!requestProcess" type="button" class="btn btn-danger" @click="deleteCalendar(delete_calendar_id)">Delete</button>
                                        <button v-else class="btn btn-primary" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                        </button>

                                        <button type="button" class="btn btn-secondary" @click="hideConfirmCalendarDeleteModal">Close</button>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        <!-- END Confirm Delete Calendar Modal -->

        <!-- Confirm Delete Event Modal -->
        <transition name="modal">
            <div v-if="showConfirmDeleteEventModal">
                <div class="modal-mask">
                    <div class="modal-wrapper">
                        <div id="confirmDeleteEventModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header text-white bg-danger">
                                        <h5 class="modal-title w-100 text-center">Confirm delete event</h5>
                                        <button type="button" class="close" @click="hideConfirmEventDeleteModal">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <p>You are about to delete event, this procedure is irreversible.</p>
                                        <p>Do you want to proceed?</p>
                                    </div>

                                    <div class="modal-footer">
                                        <button v-if="!requestProcess" type="button" class="btn btn-danger" @click="deleteEvent(delete_event_id)">Delete</button>
                                        <button v-else class="btn btn-primary" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                        </button>

                                        <button type="button" class="btn btn-secondary" @click="hideConfirmEventDeleteModal">Close</button>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
        <!-- END Confirm Delete Event Modal -->

    </div>

</template>

<script>
    export default {
        props:['data', 'jobs_status', 'user_role', 'user_id'],

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

                sortBySummaryDirection: 'asc',
                sortByEventsDirection: 'asc',
                sortByOwnerDirection: 'asc',
                sortByUpdatedDirection: 'asc',

                delete_calendar_id: '',
                delete_event_id: null,
                showConfirmDeleteCalendarModal: false,
                showConfirmDeleteEventModal: false,
            }
        },

        created() {
            this.$root.$refs.allCalendars = this;
        },

        methods: {

            filterCalendars: function(param, value) {
                this.sortedCalendars = this.calendars.filter(calendar => {
                    return calendar[param] === value;
                })
            },

            applyCalendarsTypeFilter: function(typeFilter) {
                this.calendarsTypesFilters.forEach(typeFilter => {
                    typeFilter.active = false;
                });
                typeFilter.active = true;

                //let user_id = this.user_id;
                let sortedArray = [];

                this.calendars.forEach(function(item) {
                    if (typeFilter.val === 'all') {
                        sortedArray.push(item);
                    } else if (typeFilter.val === 'owned') {
                        if (item.owned === true) {
                            sortedArray.push(item);
                        }
                    } else if (typeFilter.val === 'shared') {
                        if (item.owned === false) {
                            sortedArray.push(item);
                        }
                    }
                });
                this.sortedCalendars = sortedArray;
            },

            showAddCalendarModal: function() {
                this.$root.$refs.addEditCalendarModal.showAddCalendarModalAction();
            },

            showConfirmCalendarDeleteModal: function(id) {
                this.delete_calendar_id = id;
                // jQuery('#confirmCalendarDelete').modal('show');

                this.showConfirmDeleteCalendarModal = true;
            },
            hideConfirmCalendarDeleteModal: function() {
                this.showConfirmDeleteCalendarModal = false;
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

                axios.post('/delete-calendar', { calendar_id: id })
                .then(function (response) {
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 404) {
                        currentObj.requestDanger = response.data.data.message;
                    } else if (response.data.code === 403) {
                        currentObj.requestDanger = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        currentObj.requestSuccess = false;
                        currentObj.delete_calendar_id = '';
                        // Remove calendar from list
                        currentObj.calendars.map(function(item, key) {
                            if (item.id === id) {
                                currentObj.calendars.splice(key, 1);
                                currentObj.sortByUpdatedDirection = 'asc';
                                currentObj.sortCalendarsListByUpdated();
                            }
                        });
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function (error) {
                    currentObj.requestDanger = 'Error Request';
                })
                .then(function() {
                    currentObj.requestProcess = false;
                    currentObj.showConfirmDeleteCalendarModal = false;
                });
            },

            showConfirmEventDeleteModal: function(id) {
                this.delete_event_id = id;
                this.showConfirmDeleteEventModal = true;
            },
            hideConfirmEventDeleteModal: function() {
                this.showConfirmDeleteEventModal = false;
            },

            deleteEvent: function(id) {
                let currentObj = this;
                axios.interceptors.request.use(function (config) {
                    // Do something before request is sent
                    currentObj.requestProcess = true;
                    return config;
                }, function (error) {
                    // Do something with request error
                    return Promise.reject(error);
                });

                axios.post('/delete-event', { event_id: id })
                    .then(function (response) {
                        if (response.data.code === 401) {
                            document.location.href="/";
                        } else if (response.data.code === 403) {
                            currentObj.requestDanger = response.data.data.message;
                        } else if (response.data.code === 404) {
                            currentObj.requestDanger = response.data.data.message;
                        } else if (response.data.code === 1) {
                            currentObj.requestSuccess = response.data.data.message;
                            //currentObj.requestSuccess = false;
                            currentObj.delete_event_id = null;
                            document.getElementById("event"+id).remove();
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            currentObj.requestDanger = 'Request Error';
                        }
                    })
                    .catch(function (error) {
                        currentObj.requestProcess = false;
                        currentObj.requestDanger = 'Error Request';
                    })
                    .then(function() {
                        currentObj.showConfirmDeleteEventModal = false;
                    });
            },

            sortArray: function(array, field, direction) {
                return _.orderBy(array, field, direction);
            },

            // Sort calendar list methods
            sortCalendarsListBySummary: function() {
                let direction = this.sortBySummaryDirection === 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'name', direction);
                this.sortBySummaryDirection = direction;
            },

            sortCalendarsListByEvents: function() {
                let direction = this.sortByEventsDirection === 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'eventsCount', direction);
                this.sortByEventsDirection = direction;
            },

            sortCalendarsListByOwner: function() {
                let direction = this.sortByOwnerDirection === 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'access_role', direction);
                this.sortByOwnerDirection = direction;
            },

            sortCalendarsListByUpdated: function() {
                let direction = this.sortByUpdatedDirection === 'desc' ? 'asc' : 'desc';
                this.sortedCalendars = this.sortArray(this.calendars, 'updated_at', direction);
                this.sortByUpdatedDirection = direction;
            }
        },

        mounted() {
            this.sortCalendarsListByUpdated();
        }
    }
</script>
