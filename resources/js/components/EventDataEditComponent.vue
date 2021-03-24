<template>
    <div class="event-data-wrapper">

        <!-- Event Date Time -->
        <hr>
        <div class="row">
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Start Date</span>
                        </div>
                        <date-picker v-model="editedEventData.startDate" :config="dateOptions" readonly name="event-start-date"></date-picker>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">

                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Start Time [hours]</span>
                        </div>
                        <select v-model="editedEventData.startTimeHours" class="custom-select" name="event-start-time-hours">
                            <option value="1">01</option>
                            <option value="2">02</option>
                            <option value="3">03</option>
                            <option value="4">04</option>
                            <option value="5">05</option>
                            <option value="6">06</option>
                            <option value="7">07</option>
                            <option value="8">08</option>
                            <option value="9">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Start Time [minutes]</span>
                        </div>
                        <select v-model="editedEventData.startTimeMinutes" class="custom-select" name="event-start-time-minutes">
                            <option value="0">00</option>
                            <option value="5">05</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="35">35</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                            <option value="55">55</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Start Time [am/pm]</span>
                        </div>
                        <select v-model="editedEventData.startTimeAmPm" class="custom-select" name="event-start-time-ampm">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">End Date</span>
                        </div>
                        <date-picker v-model="editedEventData.endDate" :config="dateOptions" readonly name="event-end-date"></date-picker>
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">End Time [hours]</span>
                        </div>
                        <select v-model="editedEventData.endTimeHours" class="custom-select" name="event-end-time-hours">
                            <option value="1">01</option>
                            <option value="2">02</option>
                            <option value="3">03</option>
                            <option value="4">04</option>
                            <option value="5">05</option>
                            <option value="6">06</option>
                            <option value="7">07</option>
                            <option value="8">08</option>
                            <option value="9">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">End Time [minutes]</span>
                        </div>
                        <select v-model="editedEventData.endTimeMinutes" class="custom-select" name="event-end-time-minutes">
                            <option value="0">00</option>
                            <option value="5">05</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="35">35</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                            <option value="55">55</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="data">
                    <div class="input-group input-group-sm mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">End Time [am/pm]</span>
                        </div>
                        <select v-model="editedEventData.endTimeAmPm" class="custom-select" name="event-end-time-ampm">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                        <div class="input-group-append">
                            <label class="input-group-text"><i class="far fa-clock"></i></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Event Date Time -->
        <hr>
        <!-- Event Data -->
        <div class="row">
            <div class="col-5">
                <div class="data">
                    <div class="form-group">
                        <label><small>Location</small></label>
                        <input type="text" v-model="editedEventData.location" class="form-control form-control-sm" name="event-location">
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    <div class="form-group">
                        <label><small>Type</small></label>
                        <select v-model="editedEventData.type" class="form-control form-control-sm" name="event-type">
                            <option value="game">Game</option>
                            <option value="practice">Practice</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-5">
                <div class="data">
                    <div class="form-group">
                        <label><small>Description</small></label>
                        <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" name="event-description">
                    </div>
                </div>
            </div>
        </div>
        <!-- END Event Data -->
        <hr>
        <!-- Event Actions -->
        <div class="row">
            <div class="col-4">
                <div class="data">
                    <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="!editedEventDataValid || requestProcess" @click="submitEditSingleEvent(event.id, $event)"><i class="far fa-save"></i> Save Event Data</button>
                    <button class="btn btn-danger btn-sm pull-right btn-open" title="Cancel" :disabled="requestProcess" @click="hideEditSingleEvent"><i class="far fa-times-circle"></i> Cancel</button>
                </div>
            </div>

            <div class="col-8">
                <div class="data">
                    <span v-if="requestSuccess" class="text-success event-request-success">{{ requestSuccess }}</span>
                    <span v-if="requestError"class="text-danger event-request-error">{{ requestError }}</span>
                </div>
            </div>
        </div>
        <!-- END Event Actions -->
        <br>

    </div>
</template>

<script>

import datePicker from 'vue-bootstrap-datetimepicker';

export default {
    props: ['event'],

    components: {
        datePicker
    },

    data() {

        return {
            dateOptions: {
                format: 'M/DD/YYYY',
                useCurrent: true,
                ignoreReadonly: true
            },
            datePickerEditable: false,

            editedEventData: {
                id: null,
                startDate: null,
                startTimeHours: null,
                startTimeMinutes: null,
                startTimeAmPm: null,
                endDate: null,
                endTimeHours: null,
                endTimeMinutes: null,
                endTimeAmPm: null,
                location: null,
                type: null,
                description: null
            },

            requestProcess: false,
            requestSuccess: null,
            requestError: null,
        }
    },

    computed: {
        editedEventDataValid() {
            let result = true;
            if (!this.editedEventData.location || this.editedEventData.location === '') {
                result = false;
            }
            if (!this.editedEventData.description || this.editedEventData.description === '') {
                result = false;
            }
            return result;
        }
    },

    methods: {
        hideEditSingleEvent: function() {
            this.$parent.showEditSingleEventForm = false;
        },
        submitEditSingleEvent: function(id, event) {

            event.preventDefault();
            event.stopPropagation();

            let currentObj = this;
            // Send request
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.requestDanger = null;
                currentObj.requestSuccess = null;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });

            axios.post('/edit-single-event', currentObj.editedEventData)
            .then(function (response) {
                if (response.data.code === 401) {
                    document.location.href = "/";
                } else if (response.data.code === 404) {
                    currentObj.requestError = response.data.data.message;
                } else if (response.data.code === 1) {
                    currentObj.requestSuccess = response.data.data.message;

                    // Update Edited event data
                    currentObj.event.started_at = new Date(response.data.data.event.started_at).toLocaleString("en-US", {timeZone: "UTC"});
                    currentObj.event.ended_at = new Date(response.data.data.event.ended_at).toLocaleString("en-US", {timeZone: "UTC"});
                    currentObj.event.location = response.data.data.event.location;
                    currentObj.event.type = response.data.data.event.type;
                    currentObj.event.description = response.data.data.event.description;

                    // Hide Edit event form
                    setTimeout(function () {
                        currentObj.requestSuccess = false;
                        currentObj.hideEditSingleEvent();
                    }, 2000);

                } else {
                    currentObj.requestError = 'Request Error';
                }
            })
            .catch(function (error) {
                currentObj.requestError = 'Request Error';
            })
            .then(function () {
                currentObj.requestProcess = false;
            });
        },
    },

    mounted() {
        this.editedEventData = {
            id: this.event.id,
            startDate: this.$options.filters.formatDate(this.event.started_at),
            startTimeHours: this.$options.filters.formatHours(this.event.started_at),
            startTimeMinutes: this.$options.filters.formatMinutes(this.event.started_at),
            startTimeAmPm: this.$options.filters.formatAmPm(this.event.started_at),
            endDate: this.$options.filters.formatDate(this.event.ended_at),
            endTimeHours: this.$options.filters.formatHours(this.event.ended_at),
            endTimeMinutes: this.$options.filters.formatMinutes(this.event.ended_at),
            endTimeAmPm: this.$options.filters.formatAmPm(this.event.ended_at),
            location: this.event.location,
            type: this.event.type,
            description: this.event.description
        };
    },

    filters: {
        formatDate: function (value) {
            let date = new Date(value);
            let month = date.getMonth() + 1;
            let day = date.getDate();
            day = day < 10 ? '0' + day : day;
            let year = date.getFullYear()
            return month + '/' + day + '/' + year;
        },

        formatHours: function (value) {
            let date = new Date(value);
            let hours = date.getHours();
            hours = hours % 12;
            hours = hours ? hours : 12;
            return hours;
        },
        formatMinutes: function (value) {
            let date = new Date(value);
            let minutes = date.getMinutes();
            minutes = Math.ceil(minutes/5)*5;
            return minutes;
        },
        formatAmPm: function(value) {
            let date = new Date(value);
            let hours = date.getHours();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            return ampm;
        }
    }
}
</script>
