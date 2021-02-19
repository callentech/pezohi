<template>
	<div class="card calendar-single">
		

		<div class="card-heading">
            <div class="row align-items-center">
	            <div class="col-3">
	                {{calendar.summary}}
	            </div>
	            <div class="col-1">
	                {{ calendar.events.length }}
	            </div>
	            <div class="col-2">
	                <span v-if="calendar.accessRole == 'owner'">
	                    me
	                </span>
	                <span v-else>
	                    other
	                </span>
	            </div>
	            <div class="col-2">
	                
	                {{ calendar.lastUpdated }}
	            </div>
	            <div class="col-4 text-right actions">

	                <button type="button" class="btn btn-primary btn-sm" name="button">
	                    <i class="fas fa-user-friends"></i> Share
	                </button>

	                <button type="button" class="btn btn-outline-primary btn-sm" name="button">
	                    <i class="far fa-bell"></i> Subscribe ???
	                </button>

	                <button type="button" class="btn btn-outline-secondary btn-sm" name="button">
	                    <i class="far fa-bell"></i> Unsubscribe ???
	                </button>

	                <button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDataForm()">
	                    
						<i class="fas fa-angle-down" v-if="showBody"></i>
	                    <i class="fas fa-angle-right" v-else></i>
	                    
	                </button>

	                
	            </div>
	        </div>
	    </div>

	    <transition name="fade">
		    <div class="card-body" v-if="showBody">

		    	<div class="row align-items-center">
	                <div class="col-lg-6" >
	                	<div v-if="calendar.events.length > 5">
	                		1-5 of {{ calendar.events.length }} <i class="fa fa-angle-right"></i> <a href="#" class="btn btn-link">View all</a>
	                	</div>
	                </div>

	                <div class="col-lg-6 text-right">
	                    <button type="button"class="btn btn-outline-primary btn-sm pull-right" @click="showAddEventForm"><i class="fa fa-plus"></i> Add event</button>
	                </div>
	            </div>

	            <div class="card calendar-events">

	            	<div class="eventsDataFilters">
	            		<div class="row">
	            			
	            			<div class="col-2">
	            				<a href="#" class="sort-link">
                                	Date <i class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
                            	<a href="#" class="sort-link">
                                	Time <i class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
                            	<a href="#" class="sort-link">
	                                Address <i class="fas fa-sort-amount-down-alt float-right"></i>
	                            </a>
	                        </div>

	                        <div class="col-2">
	                        	<a href="#" class="sort-link">
                                    Event <i class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
	                        	<a href="#" class="sort-link">
                                    Notes <i class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                            <div class="col-2">
	                        	<a href="#" class="sort-link">
                                    Actions <i class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </div>

                        </div>
	            	</div>

	            	<div class="events-list">

	            		
                            
                            <div v-for="event in calendar.events">

                            	<calendars-list-event-component :event="event" ref="event"></calendars-list-event-component>
                            	
	            			</div>
	            	</div>

	            	<transition name="fade">
				  		<div class="card-footer" v-if="showNewEventDataForm">

				  			<form id="addCalendarEventForm" class="needs-validation" :action="new_event_form_action" method="POST" @submit="addEventSubmit" novalidate>

				  				<input type="hidden" name="_token" :value="csrf_token">
				  				<input type="hidden" name="calendar_id" :value="calendar.id">

					    		<div class="row">

				    				<div class="input-group input-group-sm mb-3 col-md-2">
										<date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new_event_datetime" :disabled="inputDisabled" placeholder="dd.mm.YYYY" required></date-picker>
										<div class="input-group-append">
											<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
										</div>
										<div class="invalid-feedback">Please provide a valid date.</div>
									</div>
									

					    			<div class="input-group input-group-sm mb-3 col-md-2">
										<input type="text" class="form-control" placeholder="H:MM PM - H:MM PM" :disabled="inputDisabled" required value="05:20 PM - 10:35 PM">
										<div class="invalid-feedback">Please provide a valid times.</div>
									</div>

					    			<div class="col-md-2">
					    				<input type="text" v-model="newEventData.address" name="new_event_address" class="form-control form-control-sm" placeholder="Address" :disabled="inputDisabled" required>
					    				<div class="invalid-feedback">Please provide a valid address.</div>
					    			</div>

					    			<div class="col-md-2">
					    				<select v-model="newEventData.type" name="new_event_type" class="form-control form-control-sm" :disabled="inputDisabled" required>	 <option value="" disabled selected>Select type</option>
										    <option value="game">Game</option>
										    <option value="practice">Practice</option>
									    </select>
									    <div class="invalid-feedback">Please provide a valid type.</div>
						    		</div>

					    			<div class="col-md-2">
					    				<input type="text" v-model="newEventData.notes" name="new_event_notes" class="form-control form-control-sm" placeholder="e.g. Instructions" :disabled="inputDisabled" required>
					    				<div class="invalid-feedback">Please provide a valid notes.</div>
					    			</div>

					    			<div class="col-md-2 text-right">

					    				<button v-if="!formRequestProcess" type="submit" form="addCalendarEventForm" class="btn btn-primary btn-sm" :disabled="!newEventDataValid"><i class="fas fa-check"></i></button>

					    				<!-- <button class="btn btn-primary btn-sm" @click="addEventSubmit(calendar, $event)" :disabled="!newEventDataValid"><i class="fas fa-check"></i></button> -->
					    				<button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm" :disabled="inputDisabled"><i class="fas fa-times"></i></button>
					    			</div>
				    			</div>

				    			<transition name="fade">
									<div v-if="requestSuccess" class="alert alert-success" role="alert">{{ requestSuccess }}</div>
									<div v-if="requestDanger" class="alert alert-danger" role="alert">{{ requestDanger }}</div>
								</transition>

			    			</form>
				  		</div>
			  		</transition>

	            </div>

	        </div>
	    </transition>

	</div>

</template>

<script>

	//import 'bootstrap/dist/css/bootstrap.css';

	import datePicker from 'vue-bootstrap-datetimepicker';
	import DateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
	
	export default {
		
		props:['calendar', 'new_event_form_action', 'csrf_token'],

		data() {
			return {

				inputDisabled: false,
				formRequestProcess: false,
				requestSuccess: false,
				requestDanger: false,

				showBody: false,
            	showNewEventDataForm: false,

				owner_email_address: '',
				calendar_name: '',


				inputDisabled: false,

				formRequestProcess: false,

				requestSuccess: false,
				requestDanger: false,

				newEventData: {
					dateTime: '',
					address: '',
					type: '',
					notes: ''
				},

				//date: new Date(),
			    dateOptions: {
			    	format: 'DD.MM.YYYY',
			        useCurrent: true
			    },
			}
		},

		components: {
        	datePicker,
        	DateRangePicker
        },

        computed:  {

        	newEventDataValid()  {
        		// return !(
        		// 	this.newEventData.address == '' || this.newEventData.dateTime == ''
        		// 	|| this.newEventData.type == 'none' || this.newEventData.notes == ''
        		// );

        		return true;
        	}

        },

		methods: {



			addEventSubmit: function(event) {

				event.preventDefault();
		        event.stopPropagation();

		        this.requestSuccess = false;
				this.requestDanger = false;

				let form = event.target;

				if (form.checkValidity() === false) {
					
					form.classList.add('was-validated');
				
				} else {

					let currentObj = this;

					let url = event.target.action;

					let formData = new FormData(form);

					axios.interceptors.request.use(function (config) {
					    // Do something before request is sent

					    currentObj.formRequestProcess = true;
					    currentObj.inputDisabled = true;

					    return config;

					}, function (error) {
					    // Do something with request error
					    return Promise.reject(error);
					});

					axios.post(url, formData)

					.then(function (response) {

						if (response.data.code == 1) {

							currentObj.calendar.events.push(response.data.data.event);

							currentObj.requestSuccess = 'Success create calendar';

							currentObj.newEventData.dateTime = null;
							currentObj.newEventData.address = null;
							currentObj.newEventData.type = null;
							currentObj.newEventData.notes = null;
					
				
							setTimeout(function() {
								currentObj.requestSuccess = false;
								// jQuery('#addCalendarModal').modal('hide');
								//location.reload();
							}, 3000);

						} else {

							currentObj.requestDanger = 'Error Request';

						}



					})
					
					.catch(function (error) {
						if (error.response.status == 422) {
							currentObj.requestDanger = error.response.data.message;
							form.classList.add('was-validated');
						} else {
							currentObj.requestDanger = 'Error Request';
						}

						console.log(error);
					})

					.then(function() {
						currentObj.formRequestProcess = false;
						currentObj.inputDisabled = false;
					});

				}
				

				/*
						




						

		     

		        

				let form = event.target;

				if (form.checkValidity() === false) {
					
					form.classList.add('was-validated');
				
				} else {

					let url = event.target.action;

					let currentObj = this;

					let formData = new FormData(form);

					axios.interceptors.request.use(function (config) {
					    // Do something before request is sent

					    currentObj.formRequestProcess = true;
					    currentObj.inputDisabled = true;

					    return config;
					}, function (error) {
					    // Do something with request error
					    return Promise.reject(error);
					});


					axios.post(url, formData)

					.then(function (response) {

						if (response.data.code == 1) {

							currentObj.requestSuccess = 'Success create calendar';

							currentObj.addCalendarResetForm();

							setTimeout(function() {
								currentObj.requestSuccess = false;
								jQuery('#addCalendarModal').modal('hide');
								location.reload();
							}, 3000);

						} else {

							currentObj.requestDanger = 'Error Request';

						}

					})
					
					.catch(function (error) {

						if (error.response.status == 422) {
							currentObj.requestDanger = error.response.data.message;
							form.classList.add('was-validated');
						} else {
							currentObj.requestDanger = 'Error Request';
						}

						
					})

					.then(function() {
						currentObj.formRequestProcess = false;
						// currentObj.inputDisabled = false;
					});

				}

			}






				*/
			},

			showAddEventForm: function(event) {
				event.preventDefault();
				this.showNewEventDataForm = true;
			},

			hideAddEventForm: function(event) {
				event.preventDefault();
				this.newEventData.dateTime = null;
				this.newEventData.address = null;
				this.newEventData.type = 'none';
				this.newEventData.notes = null;
				this.showNewEventDataForm = false;
			},

			toggleCalendarDataForm: function() {

				if (this.showBody) {
					this.showBody = !this.showBody;
				} else {
					this.$parent.$refs.calendar.forEach((element) => {
						element.showBody = false;
						element.showNewEventDataForm = false;
					});
					this.showBody = !this.showBody;
				}
                

				
            }


		},

		mounted() {}
	}

</script>

<!-- <script>

	import datePicker from 'vue-bootstrap-datetimepicker';
	import DateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

    export default {

        props:['calendar'],

        data() {
            return {
            	showBody: false,
            	showNewEventDataForm: false,
            	
            	newEventData: {
					dateTime: '01.02.2021',
					address: 'Address',
					type: 'game',
					notes: 'Notes'
				},

				components: {
		        	datePicker,
		        	DateRangePicker
		        },

		        dateOptions: {
			    	format: 'DD.MM.YYYY',
			        useCurrent: true
			    },
        

                // calendars: this.data,
                // calendarsTypesFilters: [
                //     { title: 'All calendars', val: 'all', active: true }, 
                //     { title: 'Owned by me', val: 'owned', active: false},
                //     { title: 'Shared with me', val: 'shared', active: false}
                // ]
            }
        },

        methods: {
            // applyCalendarsTypeFilter: function(typeFilter) {

            //     this.calendarsTypesFilters.forEach(typeFilter => {
            //         typeFilter.active = false;
            //     });

            //     typeFilter.active = true;
            // },

            // showAddCalendarModal: function() {
            //     jQuery('#addCalendarModal').modal('show');
            // },

            toggleCalendarDataForm: function() {
                this.$parent.$refs.calendar.forEach((element) => {
					element.showBody = false;
					element.showNewEventDataForm = false;
				});
				this.showBody = !this.showBody;
            }

        },

        mounted() {

        }
    }
</script> -->