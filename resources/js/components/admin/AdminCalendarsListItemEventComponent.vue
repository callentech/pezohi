<template>
    <div :id="'event'+event.id"  v-if="event.status === 'cancelled' || event.status === 'over'" class="card calendar-single event-cancelled">
        <div class="row text-muted">
            <div class="col-3">
                <div class="data event-datetime">
                    <del>{{ event.started_at|formatDate }}</del>
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    <del>{{ event.location|sliceString }}</del>
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    <del>{{ event.type|capitalize }}</del>
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    <del>{{ event.description|sliceString }}</del>
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    <span class="badge badge-secondary event-status-badge">{{event.status}}</span>
                </div>
            </div>

            <div class="col-1">
                <div class="data text-right">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-open" title="Delete" @click="$event.stopPropagation(), showConfirmEventDeleteModal(event.id);"><i class="far fa-trash-alt"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div :id="'event'+event.id"  v-else class="card calendar-single event-active">
        <div class="row">
            <div class="col-3">
                <div class="data event-datetime">
                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{
                        event.ended_at|formatDate
                    }} {{ event.ended_at|formatTime }}
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    <a title="Details" href="javascript:void(0)" @click="$event.stopPropagation(), showEventDetails=!showEventDetails">
                        {{ event.location|sliceString }} <i class="fas fa-angle-down"></i>
                    </a>
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    {{ event.type|capitalize }}
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    <a title="Details" href="javascript:void(0)" @click="$event.stopPropagation(), showEventDetails=!showEventDetails">
                        {{ event.description|sliceString }} <i class="fas fa-angle-down"></i>
                    </a>
                </div>
            </div>
            <div class="col-1">
                <div class="data">
                    <span v-if="event.status === 'over'" class="badge badge-secondary event-status-badge">{{event.status}}</span>
                    <span v-if="event.status === 'confirmed'" class="badge badge-success event-status-badge">{{event.status}}</span>
                    <span v-if="event.status === 'tentative'" class="badge badge-warning event-status-badge">{{event.status}}</span>
                </div>
            </div>

            <div class="col-2">


                <div class="data text-right">
                    <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="Edit" @click="$event.stopPropagation(), showEditSingleEvent(event.id)"><i class="far fa-edit"></i></button>

                    <div @mouseover="showEventDropdownActions=true" @mouseleave="showEventDropdownActions=false" @click="$event.stopPropagation()" class="dropdown-event-actions">
                        <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="More"><i class="fas fa-ellipsis-v"></i></button>
                        <transition name="fade">
                            <div class="items" v-if="showEventDropdownActions">
                                <a href="javascript:void(0)" @click="$event.stopPropagation(), duplicateEventAction();"><i class="far fa-clone"></i> Duplicate</a>
                                <a href="javascript:void(0)" @click="$event.stopPropagation(), cancelEventAction(event.id);"><i class="fas fa-ban"></i> Cancel</a>
                                <a href="javascript:void(0)" @click="$event.stopPropagation(), showConfirmEventDeleteModal(event.id);"><i class="far fa-trash-alt"></i> Delete</a>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
        <transition name="fade">
            <div v-if="showEventDetails" class="row event-details">
                <div class="col-12">
                    <div>
                        <div class="card border-secondary">
                            <div class="card-header bg-transparent border-secondary">
                                <strong>Address : </strong>
                            </div>
                            <div class="card-body text-secondary">
                                <a title="Copy event address" href="javascript:void(0)" @click="copyEventAddress">{{ event.location }}</a>
                            </div>
                        </div>

                        <div class="card border-secondary">
                            <div class="card-header bg-transparent border-secondary">
                                <strong>Notes : </strong>
                            </div>
                            <div class="card-body text-secondary">
                                {{ event.description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
        <transition name="fade">
            <div v-if="showEditSingleEventForm">
                <event-data-edit-component :event="event" ref="event"></event-data-edit-component>
            </div>
        </transition>

    </div>
</template>

<script>

import moment from 'moment';

export default {

    props: ['event', 'calendar'],

    data() {

        return {
            showEditSingleEventForm: false,
            showEventDetails: false,
            showEventDropdownActions: false,

            editedEventData: {
                id: null,
                dateTime: '',
                location: '',
                type: '',
                description: ''
            },

            requestProcess: false,
            requestSuccess: false,
            requestDanger: false,

            dateOptions: {
                format: 'M/DD/YYYY',
                useCurrent: true
            },

            moment: moment
        }
    },

    methods: {

        duplicateEventAction: function() {
            this.showEventDropdownActions = false;
            this.event.duplicate_event_id = this.event.id;
            this.showEditSingleEventForm = true;
        },

        cancelEventAction: function(event_id) {
            this.showEventDropdownActions = false;
            let currentObj = this;
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.$parent.$parent.requestDanger = false;
                currentObj.$parent.$parent.requestSuccess = false;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });
            axios.post('/cancel-event', { event_id: event_id })
                .then(function (response) {
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 404) {
                        currentObj.$parent.$parent.requestDanger = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.$parent.$parent.requestSuccess = response.data.data.message;
                        currentObj.event.status = 'cancelled';
                    } else {
                        currentObj.$parent.$parent.requestDanger = 'Request Error';
                    }
                })
                .catch(function (error) {
                    currentObj.$parent.$parent.requestDanger = 'Error Request';
                })
                .then(function() {
                    currentObj.requestProcess = false;
                });
        },

        copyEventAddress: function() {
            // if (this.showEditSingleEventForm || this.showEventDetails) {
            //     return false;
            // }

            if (this.showEditSingleEventForm) {
                return false;
            }
            let input_temp = document.createElement('textarea');
            input_temp.innerHTML = this.event.location;
            document.body.appendChild(input_temp);
            input_temp.select();
            input_temp.setSelectionRange(0, 99999);
            document.execCommand('copy');
            document.body.removeChild(input_temp);
            this.$parent.infoModalHtml = '<p>Event Address was copied to your clipboard</p><input type="text" value="'+input_temp.innerHTML+'" readonly>';
            this.$parent.showInfoModal = true;
        },
        showEditSingleEvent: function () {
            this.$parent.$refs.event.forEach((element) => {
                element.showEditSingleEventForm = false;
            });
            this.showEditSingleEventForm = true;
            this.showEventDropdownActions = false;
        },
        hideEditSingleEvent: function () {
            this.showEditSingleEventForm = false;
        },

        showConfirmEventDeleteModal: function(id) {
            this.showEventDropdownActions = false;
            this.$parent.$parent.delete_event_id = id;
            this.$parent.$parent.showConfirmDeleteEventModal = true;
        },
    },
    mounted() {

        this.editedEventData = {
            id: this.event.id,
            dateTime: this.event.dateTime,
            location: this.event.location,
            type: this.event.type,
            description: this.event.description
        };

        if (this.moment(this.event.ended_at).isBefore(new Date())) {
            this.event.status = 'over';
        }
    },
    filters: {
        formatDate: function (value) {
            let date = new Date(value);
            let month = date.getMonth() + 1;
            let day = date.getDate();
            let year = date.getFullYear()
            return month + '/' + day + '/' + year;
        },

        formatTime: function(value) {
            let dateTime = new Date(Date.parse(value));
            return moment(dateTime).format('hh:mm a').toUpperCase();
        },

        sliceString: function (value) {
            if (value && value.length > 10) {
                let sliced = value.slice(0, 10);
                if (sliced.length < value.length) {
                    sliced += '...';
                }
                return sliced;
            } else {
                return value;
            }
        },

        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        },

        date: function (date) {
            let day = date.getDate() >= 10 ? date.getDate() : '0' + date.getDate();
            let dateHours = date.getHours();
            let dateAmpm = dateHours >= 12 ? 'PM' : 'AM';
            dateHours = dateHours % 12;
            dateHours = dateHours ? dateHours : 12;
            let dateMinutes = date.getMinutes();
            dateMinutes = dateMinutes < 10 ? '0' + dateMinutes : dateMinutes;
            return (date.getMonth() + 1) + '/' + day + '/' + date.getFullYear() + ' ' + dateHours + ':' + dateMinutes + ' ' + dateAmpm;
        }
    }
}
</script>
