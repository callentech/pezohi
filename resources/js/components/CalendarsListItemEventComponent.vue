<template>
    <div :id="'event'+event.id"  v-if="event.status === 'cancelled' || event.status === 'over'" class="card calendar-single event-cancelled">
        <div class="row text-muted">
            <div class="col-3">
                <div class="data" title>
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
                    <button title="Delete" class="btn btn-outline-danger btn-sm" @click="showConfirmEventDeleteModal(event.id)"><i class="far fa-trash-alt"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div :id="'event'+event.id"  v-else class="card calendar-single event-active" @click="copyEventAddress">
        <div class="row">
            <div class="col-3">
                <div class="data" title>
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
            <div class="col-2">
                <div class="data">
                    <span v-if="event.status === 'over'" class="badge badge-secondary event-status-badge">{{event.status}}</span>
                    <span v-if="event.status === 'confirmed'" class="badge badge-success event-status-badge">{{event.status}}</span>
                    <span v-if="event.status === 'tentative'" class="badge badge-warning event-status-badge">{{event.status}}</span>
                </div>
            </div>
            <div class="col-1">
                <div class="data text-right">
                    <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="Edit" @click="$event.stopPropagation(), showEditSingleEvent(event.id)"><i class="far fa-edit"></i></button>

                    <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="More" @click="$event.stopPropagation()"><i class="fas fa-ellipsis-v"></i></button>
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
                                {{ event.location }}
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

    props: ['event'],

    data() {

        return {
            showEditSingleEventForm: false,
            showEventDetails: false,

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
        copyEventAddress: function() {
            if (this.showEditSingleEventForm || this.showEventDetails) {
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

            // init location input google maps
            //const autocomplete = new google.maps.places.Autocomplete(this.$refs["origin"]);
        },
        hideEditSingleEvent: function () {
            this.showEditSingleEventForm = false;
        },

        showConfirmEventDeleteModal: function(id) {

            console.log(this.$parent.$parent);
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

        formatTime: function (value) {
            let date = new Date(value);
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        },

        sliceString: function (value) {
            let sliced = value.slice(0, 10);
            if (sliced.length < value.length) {
                sliced += '...';
            }
            return sliced;
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
