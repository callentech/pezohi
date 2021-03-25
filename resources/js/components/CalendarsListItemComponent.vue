<template>

	<div class="card calendar-single">

		<div class="card-heading">

			<div class="row align-items-center">

				<div class="col-2">
		            {{ calendar.name }}
		        </div>

		        <div class="col-2">
		            {{ calendar.eventsCount }}
		        </div>

		        <div class="col-2">
	                <span v-if="calendar.access_role == 'owner'">Me ({{ calendar.access_role }})</span>
	                <span v-else>Other ({{ calendar.access_role }})</span>
	            </div>

<!--	            <div class="col-2">-->
<!--	                <span v-if="calendar.updated == null"></span>-->
<!--                    <span v-else>{{ calendar.updated|formatDate }}</span>-->
<!--	            </div>-->

                <div class="col-2">

                    {{ calendar.updated_at|formatDate }}
                </div>

	            <div class="col-4 text-right actions">

	            	<button type="button" class="btn btn-primary btn-sm" @click="shareCalendar(calendar.publicUrl)">
	                    <i class="fas fa-user-friends"></i> Share
	                </button>

	                <!-- <button type="button" class="btn btn-outline-primary btn-sm" name="button">
	                    <i class="far fa-bell"></i> Subscribe ???
	                </button> -->

	                <button type="button" class="btn btn-outline-danger btn-sm" name="button">
	                    <i class="far fa-bell"></i> Unsubscribe
	                </button>

					<div class="dropdown-calendar-actions">
						<button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDropdownActions()">
							<i class="fas fa-ellipsis-v"></i>
		                </button>

		                <transition name="fade">
			                <div class="items" v-if="showCalendarDropdownActions">
			                	<a href="javascript:void(0)" @click="showEditCalendarModalAction(calendar.id)"><i class="far fa-edit"></i> Edit</a>
			                	<a href="javascript:void(0)" @click="showDuplicateCalendarModal(calendar.id)"><i class="far fa-clone"></i> Duplicate</a>
			                	<a href="javascript:void(0)" @click="showConfirmCalendarDelete(calendar.id)"><i class="far fa-trash-alt"></i> Delete</a>
			                </div>
			            </transition>

					</div>

					<button type="button" v-if="showBody" class="btn btn-light btn-sm pull-right btn-opened" @click="toggleCalendarDataForm()">
						<i class="fas fa-angle-down"></i>

	                </button>
	                <button type="button" v-else class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDataForm()">
	                    <i class="fas fa-angle-right"></i>
	                </button>

	            </div>

		    </div>


		</div>

		<!-- Calendar details -->
		<transition name="fade">
			<div class="card-body" v-if="showBody">

				<div class="row align-items-center">
	                <div class="col-lg-6" >
<!--	                	<div v-if="calendar.events.length > 5">-->
<!--	                		1-5 of {{ calendar.events.length }} <i class="fa fa-angle-right"></i> <a href="#" class="btn btn-link">View all</a>-->
<!--	                	</div>-->

                        <div>
                            1-5 of {{ calendar.events.length }} <i class="fa fa-angle-right"></i> <a href="#">View all</a>
                        </div>
	                </div>

	                <div class="col-lg-6 text-right">
	                    <button type="button"class="btn btn-outline-primary btn-sm pull-right" @click="showAddEventForm(calendar.id)"><i class="fa fa-plus"></i> Add event</button>
	                </div>
	            </div>

	            <div class="card calendar-events">

	            	<div class="eventsDataFilters">
	            		<div class="row">

	            			<div class="col-4">
	            				<a href="javascript:void(0)" class="sort-link" @click="sortEventsListByDate">
                                	Event Date and Time
                                    <i v-if="sortByDateDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
                            	<a href="javascript:void(0)" class="sort-link" @click="sortEventsListByLocation">
	                                Address
                                    <i v-if="sortByLocationDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
	                            </a>
	                        </div>

	                        <div class="col-2">
                                <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByType">
                                    Event
                                    <i v-if="sortByTypeDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
                                <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByDescription">
                                    Notes
                                    <i v-if="sortByDescriptionDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
	                        	<a href="javascript:void(0)" class="sort-link">
                                    Actions
                                </a>
                            </div>

                        </div>
	            	</div>

	            	<div class="events-list">
                        <div v-for="event in sortedEvents">
                            <calendars-list-item-event-component :event="event" ref="event"></calendars-list-item-event-component>
                        </div>
	            	</div>

                    <transition name="fade">
                        <div class="card-footer" v-if="showNewEventDataForm">

                            <form id="addCalendarEventForm" class="needs-validation" @submit="addEventSubmit" novalidate>

                                <input type="hidden" name="calendar_id" :value="calendar.id">

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
                                                <input type="text" v-model="editedEventData.location" class="form-control form-control-sm" name="event_location">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="data">
                                            <div class="form-group">
                                                <label><small>Type</small></label>
                                                <select v-model="editedEventData.type" class="form-control form-control-sm" name="event_type">
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
                                                <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" name="event_description">
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
                                            <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="!newEventDataValid || requestProcess"><i class="far fa-save"></i> Save Event Data</button>
                                            <button class="btn btn-danger btn-sm pull-right btn-open" title="Cancel" :disabled="requestProcess" @click="hideAddEventForm"><i class="far fa-times-circle"></i> Cancel</button>
                                        </div>
                                    </div>

                                    <div class="col-8">
                                        <div class="data">
                                            <span v-if="requestSuccess" class="text-success event-request-success">{{ requestSuccess }}</span>
                                            <span v-if="requestDanger"class="text-danger event-request-error">{{ requestDanger }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Event Actions -->
                            </form>
                        </div>
                    </transition>

	            </div>
			</div>
		</transition>
        <!-- END Calendar details -->

		<!-- Share Calendar Message modal -->
		<div v-if="showInfoModal">
			<transition name="modal">
				<div class="modal-mask">
	        		<div class="modal-wrapper">
	        			<div class="message-modal" tabindex="-1" role="dialog">
				  			<div class="modal-dialog" role="document">
				    			<div class="modal-content">
				      				<div class="modal-header">
				       	 				<h5 class="modal-title">Information</h5>
								        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="infoModalText='', showInfoModal=false">
								          <span aria-hidden="true">&times;</span>
								        </button>
								    </div>
								    <div v-html="infoModalHtml" class="modal-body">
								    </div>
								    <div class="modal-footer">
								    	<button type="button" class="btn btn-secondary" @click="infoModalText='', showInfoModal=false">Close</button>
								    </div>
				    			</div>
				  			</div>
						</div>
	        		</div>
	      		</div>
			</transition>
		</div>
		<!-- END Share Calendar Message modal -->

	</div>

</template>

<script>

import datePicker from 'vue-bootstrap-datetimepicker';
import DateRangePicker from 'vue2-daterange-picker'

import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

//import VueGoogleAutocomplete from 'vue-google-autocomplete'

	export default {

		props:['calendar', 'jobs_status'],

		components: {
			datePicker,
			//DateRangePicker
			//VueGoogleAutocomplete
		},

		data() {
			let startDate = new Date();
            let endDate = new Date();
            endDate.setDate(endDate.getDate() + 6);

            return {

                dateRange: { startDate, endDate },
                timePicker: true,
                dateFormat: 'M/DD/YYYY',
                showWeekNumbers: false,
                singleDatePicker: false,
                showDropdowns: false,
                ranges: false,
                showCalendar: true,



				showBody: false,
				showCalendarDropdownActions: false,
				showNewEventDataForm: false,

                dateOptions: {
                    format: 'M/DD/YYYY',
                    useCurrent: true,
                    ignoreReadonly: true
                },

				requestProcess: false,

				requestDanger: false,
				requestSuccess: false,

				newEventData: {
					dateTime: '',
					address: '',
					type: '',
					notes: ''
				},

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

				calendar_id: null,

				datePicker: null,
				input: null,
				address: '',

                showInfoModal: false,
				infoModalHtml: '',

                sortByDateDirection: 'desc',
                sortByLocationDirection: 'desc',
                sortByTypeDirection: 'desc',
                sortByDescriptionDirection: 'desc',

                sortedEvents: []
			}
		},

		computed:  {
            newEventDataValid() {
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

			toggleCalendarDataForm: function() {
				if (this.showBody) {
					this.showBody = !this.showBody;
				} else {
					this.$parent.$refs.calendar.forEach((element) => {
						element.showBody = false;
						element.showNewEventDataForm = false;
						element.showCalendarDropdownActions = false;
					});
					this.showBody = !this.showBody;
				}
			},

			toggleCalendarDropdownActions: function() {
				if (this.showCalendarDropdownActions) {
					this.showCalendarDropdownActions = !this.showCalendarDropdownActions;
					this.showBody = false;
					this.showNewEventDataForm = false;
				} else {
					this.$parent.$refs.calendar.forEach((element) => {
						element.showCalendarDropdownActions = false;
						element.showBody = false;
						element.showNewEventDataForm = false;
					});
					this.showCalendarDropdownActions = !this.showCalendarDropdownActions;
				}
			},

            showEditCalendarModalAction: function(id) {
                this.showBody = false;
                this.showCalendarDropdownActions = false;
                this.$root.$refs.addEditCalendarModal.showEditCalendarModalAction(id);
            },

			showDuplicateCalendarModal: function(id) {
				this.showBody = false;
				this.showCalendarDropdownActions = false;
				this.$root.$refs.addEditCalendarModal.showDuplicateCalendarModalAction(id);
			},

			showAddEventForm: function(calendar_id) {
				this.calendar_id = calendar_id;
				this.showNewEventDataForm = true;
			},

			hideAddEventForm: function() {
				this.calendar_id = null;
				this.showNewEventDataForm = false;
			},

			showConfirmCalendarDelete: function(id) {
				this.showBody = false;
				this.showCalendarDropdownActions = false;
				this.$root.$refs.allCalendars.showConfirmCalendarDeleteModal(id);
            },

			shareCalendar: function(url) {
				console.log(url);
				let input_temp = document.createElement('textarea');
				input_temp.innerHTML = url;
				document.body.appendChild(input_temp);
				input_temp.select();
				input_temp.setSelectionRange(0, 99999);
				document.execCommand('copy');
				document.body.removeChild(input_temp);

				this.infoModalHtml = '<p>Public link to calendar was copied to your clipboard</p><input type="text" value="'+url+'" readonly>';
				this.showInfoModal = true;
			},

            addEventSubmit: function(event) {
                event.preventDefault();
                event.stopPropagation();

                let currentObj = this;
                let form = document.getElementById('addCalendarEventForm');
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                axios.interceptors.request.use(function (config) {
                    // Do something before request is sent
                    currentObj.requestProcess = true;
                    return config;
                }, function (error) {
                    // Do something with request error
                    return Promise.reject(error);
                });

                let started_at = this.getDateTime(currentObj.editedEventData.startDate, currentObj.editedEventData.startTimeHours, currentObj.editedEventData.startTimeMinutes, currentObj.editedEventData.startTimeAmPm);
                let ended_at = this.getDateTime(currentObj.editedEventData.endDate, currentObj.editedEventData.endTimeHours, currentObj.editedEventData.endTimeMinutes, currentObj.editedEventData.endTimeAmPm);

                let formData = new FormData(form);
                formData.append('event_started_at', started_at);
                formData.append('event_ended_at', ended_at);

                axios.post('/new-single-event', formData)
                .then(function(response) {
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 404) {
                        currentObj.requestDanger = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function (error) {
                    currentObj.requestDanger = 'Request Error';
                })
                .then(function() {
                    currentObj.requestProcess = false;
                });
            },

			/**
            * When the location found
            * @param {Object} addressData Data of the found location
            * @param {Object} placeResultData PlaceResult object
            * @param {String} id Input container ID
            */
            // getAddressData: function (addressData, placeResultData, id) {
            //     this.address = addressData;
            // }

            // Sort events list methods
            // sortCalendarsListBySummary: function() {
            //     let direction = this.sortBySummaryDirection == 'desc' ? 'asc' : 'desc';
            //     this.sortedCalendars = this.sortArray(this.calendars, 'name', direction);
            //     this.sortBySummaryDirection = direction;
            // },
            sortEventsListByDate: function() {
                this.sortByDateDirection = this.sortByDateDirection === 'desc' ? 'asc' : 'desc';
                this.sortedEvents = this.sortArray(this.calendar.events, 'started_at', this.sortByDateDirection);
            },
            sortEventsListByLocation: function() {
                this.sortByLocationDirection = this.sortByLocationDirection === 'desc' ? 'asc' : 'desc';
                this.sortedEvents = this.sortArray(this.calendar.events, 'location', this.sortByLocationDirection);
            },
            sortEventsListByType: function() {
                this.sortByTypeDirection = this.sortByTypeDirection === 'desc' ? 'asc' : 'desc';
                this.sortedEvents = this.sortArray(this.calendar.events, 'type', this.sortByTypeDirection);
            },
            sortEventsListByDescription: function() {
                this.sortByDescriptionDirection = this.sortByDescriptionDirection === 'desc' ? 'asc' : 'desc';
                this.sortedEvents = this.sortArray(this.calendar.events, 'location', this.sortByDescriptionDirection);
            },

            sortArray: function(array, field, direction) {
                return _.orderBy(array, field, direction);
            },

            getDateTime: function(date, hours, minutes, ampm) {
                let dateArray = date.split ("/");
                let day = dateArray[1];
                let month = dateArray[0] < 10 ? '0' + dateArray[0] : dateArray[0];
                hours = parseInt(hours);
                minutes = parseInt(minutes);
                if (hours === 12 && ampm === 'PM') {
                    hours = 0;
                } else {
                    hours = ampm === 'PM' ? hours + 12 : hours;
                }
                hours = hours < 10 ? '0' + hours : hours;
                minutes = minutes < 10 ? '0' + minutes : minutes;
                let result = dateArray[2] + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':00';
                return result;
            }
		},

		filters: {
			capitalize: function (value) {
				if (!value) return ''
					value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			},

			formatDate: function(value) {

				let today = new Date();
				let date = new Date(value);

				let outputDate = '';

				let fromDate = parseInt(date.getTime()/1000);
				let toDate = parseInt(today.getTime()/1000);
				let timeDiff = Math.ceil((toDate - fromDate)/3600);

				let dateHours = date.getHours();
				dateHours = dateHours % 12;
				dateHours = dateHours ? dateHours : 12;
				let dateMinutes = date.getMinutes();
				dateMinutes = dateMinutes < 10 ? '0'+dateMinutes : dateMinutes;
				let dateAmpm = dateHours >= 12 ? 'PM' : 'AM';

				if (timeDiff <= 1) {
					let fromDateMinutes = parseInt(date.getTime()/1000*60);
					let toDateMinutes = parseInt(today.getTime()/1000*60);
					let minutesDiff = Math.ceil((toDateMinutes - fromDateMinutes)/3600);
					if (minutesDiff <= 1) {
						outputDate = minutesDiff + ' minute ago';
					} else {
						outputDate = minutesDiff + ' minutes ago';
					}
				} else if (timeDiff == 1) {
					outputDate = timeDiff + ' hour ago';
				} else if (timeDiff <= 24) {
					outputDate = timeDiff + ' hours ago';
				} else if (timeDiff < 48) {
					outputDate = 'Yesterday, ' + dateHours + ':' + dateMinutes + ' '+ dateAmpm;
				} else if (timeDiff > 48) {
					let day = date.getDate() >= 10 ? date.getDate() : '0'+date.getDate();
					//let month = parseInt(date.getMonth()+1) < 10 ? '0'+(date.getMonth()+1) : (date.getMonth()+1);
					//outputDate = day + '.' + month + '.' + date.getFullYear();
					outputDate = (date.getMonth()+1)+'/'+day+'/'+date.getFullYear();
				}
				return outputDate;
			},

			date: function(date) {
                let day = date.getDate() >= 10 ? date.getDate() : '0'+date.getDate();
                let dateHours = date.getHours();
                let dateAmpm = dateHours >= 12 ? 'PM' : 'AM';
                dateHours = dateHours % 12;
                dateHours = dateHours ? dateHours : 12;
                let dateMinutes = date.getMinutes();
                dateMinutes = dateMinutes < 10 ? '0'+dateMinutes : dateMinutes;
                return (date.getMonth()+1)+'/'+day+'/'+date.getFullYear()+' '+dateHours+':'+dateMinutes+' '+dateAmpm;
            }

		},

		mounted() {
            this.sortEventsListByDate();
		}

	}
</script>
