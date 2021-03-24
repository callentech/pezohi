<template>
    <div class="card calendar-single">



        <div class="row">
            <div class="col-4">
                <div class="data" title>
                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{
                        event.ended_at|formatDate
                    }} {{ event.ended_at|formatTime }}
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    <a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a>
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    {{ event.type|capitalize }}
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    <a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a>
                </div>
            </div>
            <div class="col-2">
                <div class="data text-right">
                    <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="Edit"
                            @click="showEditSingleEvent(event.id)"><i class="far fa-edit"></i></button>
                    <button type="button" class="btn btn-outline-primary btn-sm pull-right btn-open" title="More"><i
                        class="fas fa-ellipsis-v"></i></button>
                </div>

            </div>
        </div>

        <transition name="fade">
            <div v-if="showEditSingleEventForm">
                <event-data-edit-component :event="event" ref="event"></event-data-edit-component>
            </div>
        </transition>



    </div>
</template>

<script>

import datePicker from 'vue-bootstrap-datetimepicker';
// import dateRangePicker from 'vue2-daterange-picker'
//
// import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
// import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

export default {

    props: ['event'],

    components: {
        datePicker,
        //dateRangePicker
    },

    data() {

        let startDate = new Date();
        let endDate = new Date();
        endDate.setDate(endDate.getDate() + 6);

        return {

            dateRange: {startDate, endDate},
            timePicker: true,
            dateFormat: 'M/DD/YYYY',
            showWeekNumbers: false,
            singleDatePicker: false,
            showDropdowns: false,
            ranges: false,
            showCalendar: true,

            showEditSingleEventForm: false,

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
        }
    },

    computed: {
        editedEventDataValid() {
            // return !(
            //     this.editedEventData.location === '' || this.editedEventData.dateTime === ''
            //     || this.editedEventData.type === 'none' || this.editedEventData.description === ''
            // );
        }
    },
    methods: {
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

        /*
        submitEditSingleEvent: function (event_id, event) {
            event.preventDefault();
            this.requestProcess = true;

            let currentObj = this;
            // Send request
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.requestDanger = '';
                currentObj.requestSuccess = '';
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });

            currentObj.editedEventData.dateTime = this.dateRange;

            axios.post('/edit-single-event', currentObj.editedEventData)
                .then(function (response) {
                    console.log(response.data.code);

                    if (response.data.code === 401) {
                        document.location.href = "/";
                    } else if (response.data.code === 404) {
                        currentObj.requestDanger = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        // Update table event data
                        currentObj.event.started_at = currentObj.$options.filters.formatDate(currentObj.editedEventData.dateTime.startDate);
                        currentObj.event.ended_at = currentObj.$options.filters.formatDate(currentObj.editedEventData.dateTime.endDate);
                        currentObj.event.location = currentObj.editedEventData.location;
                        currentObj.event.type = currentObj.editedEventData.type;
                        currentObj.event.description = currentObj.editedEventData.description;

                        // Hide edit event form
                        setTimeout(function () {
                            currentObj.requestSuccess = false;
                            currentObj.hideEditSingleEvent();
                        }, 2000);
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function (response) {
                    currentObj.requestDanger = 'Request Error';
                })
                .then(function () {
                    currentObj.requestProcess = false;
                });
        }

         */
    },
    mounted() {
        this.event.dateTime = this.$options.filters.formatDate(this.event.started_at);
        this.editedEventData = {
            id: this.event.id,
            dateTime: this.event.dateTime,
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
            let year = date.getFullYear()
            return month + '/' + day + '/' + year;
        },

        formatTime: function (value) {
            let date = new Date(value);
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let ampm = hours >= 12 ? 'pm' : 'am';
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
