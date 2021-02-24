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
	                <span v-if="calendar.accessRole == 'owner'">Me</span>
	                <span v-else>Other</span>
	            </div>

	            <div class="col-2">
	                calendar.lastUpdated
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

					<div class="dropdown-calendar-actions">
						<button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDropdownActions()">
							<i class="fas fa-ellipsis-v"></i>
		                </button>

		                <transition name="fade">
			                <div class="items" v-if="showCalendarDropdownActions">
			                	<a href="javascript:void(0)" @click="showAddEditCalendarModal(calendar.id)"><i class="far fa-edit"></i> Edit</a>
			                	<a href=""><i class="far fa-clone"></i> Duplicate</a>
			                	<a href="javascript:void(0)" @click="showConfirmCalendarDelete(calendar.id)"><i class="far fa-trash-alt"></i> Delete</a>
			                </div>
			            </transition>
						
					</div>

					<button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDataForm()">
						<i class="fas fa-angle-down" v-if="showBody"></i>
	                    <i class="fas fa-angle-right" v-else></i>
	                </button>
	            	
	            </div>

		    </div>


		</div>

		<!-- Calendar details -->
		<transition name="fade">
			<div class="card-body" v-if="showBody">

				<div class="row align-items-center">
	                <div class="col-lg-6" >
	                	<div v-if="calendar.events.length > 5">
	                		1-5 of {{ calendar.events.length }} <i class="fa fa-angle-right"></i> <a href="#" class="btn btn-link">View all</a>
	                	</div>
	                </div>

	                <div class="col-lg-6 text-right">
	                    <button type="button"class="btn btn-outline-primary btn-sm pull-right" @click="showAddEventForm(calendar.id)"><i class="fa fa-plus"></i> Add event</button>
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

	            	
				  		<div class="card-footer" v-if="showNewEventDataForm">

				  			<form id="addCalendarEventForm" class="needs-validation" action="/calendar-new-event" @submit="addEventSubmit" novalidate>

				  				
				  				<input type="hidden" name="calendar_id" :value="calendar.id">

					    		<div class="row">

				    				<div class="input-group input-group-sm mb-3 col-md-2">
										<date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new_event_datetime" :disabled="requestProcess" placeholder="dd.mm.YYYY" required></date-picker>
										<div class="input-group-append">
											<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
										</div>
										<div class="invalid-feedback">Please provide a valid date.</div>
									</div>
									

					    			<div class="input-group input-group-sm mb-3 col-md-2">
										<input type="text" class="form-control" placeholder="H:MM PM - H:MM PM" :disabled="requestProcess" required value="05:20 PM - 10:35 PM">
										<div class="invalid-feedback">Please provide a valid times.</div>
									</div>

					    			<div class="col-md-2">
					    				<input type="text" v-model="newEventData.address" name="new_event_address" class="form-control form-control-sm" placeholder="Address" required>
					    				<div class="invalid-feedback">Please provide a valid address.</div>
					    			</div>

					    			<div class="col-md-2">
					    				<select v-model="newEventData.type" name="new_event_type" class="form-control form-control-sm" required>
					    					<option value="" disabled selected>Select type</option>
										    <option value="game">Game</option>
										    <option value="practice">Practice</option>
									    </select>
									    <div class="invalid-feedback">Please provide a valid type.</div>
						    		</div>

					    			<div class="col-md-2">
					    				<input type="text" v-model="newEventData.notes" name="new_event_notes" class="form-control form-control-sm" placeholder="e.g. Instructions" required>
					    				<div class="invalid-feedback">Please provide a valid notes.</div>
					    			</div>

					    			<div class="col-md-2 text-right">
					    				<button type="submit" form="addCalendarEventForm" class="btn btn-primary btn-sm" :disabled="!newEventDataValid || requestProcess"><i class="fas fa-check"></i></button>
					    				<!-- <button class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>  -->
					    				<button type="button" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></button>
					    			</div>
				    			</div>

				    			
									<div v-if="requestSuccess" class="alert alert-success" role="alert">{{ requestSuccess }}</div>
									<div v-if="requestDanger"class="alert alert-danger" role="alert">{{ requestDanger }}</div>
								

			    			</form>
				  		</div>
			  		

	            </div>
			</div>
		</transition>
		<!-- END Calendar details -->
	</div>

</template>

<script>

	import datePicker from 'vue-bootstrap-datetimepicker';
	import DateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

	export default {

		props:['calendar'],

		components: {
			datePicker,
			DateRangePicker
		},

		data() {
			return {
				showBody: false,
				showCalendarDropdownActions: false,
				showNewEventDataForm: false,

				dateOptions: {
					format: 'DD.MM.YYYY',
					useCurrent: true
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

				calendar_id: null
			}
		},

		computed:  {
			newEventDataValid() {
				return !(
					this.newEventData.address == '' || this.newEventData.dateTime == '' 
					|| this.newEventData.type == 'none' || this.newEventData.notes == ''
					);
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

			showAddEditCalendarModal: function(id) {
				this.showBody = false;
				this.showCalendarDropdownActions = false;
				this.$root.$refs.addEditCalendarModal.showEditCalendarModal(id);
			},

			showAddEventForm: function(calendar_id) {
				this.calendar_id = calendar_id;
				this.showNewEventDataForm = true;

			},

			showConfirmCalendarDelete: function(id) {
				this.showBody = false;
				this.showCalendarDropdownActions = false;
				this.$root.$refs.allCalendars.showConfirmCalendarDelete(id);
            },

            addEventSubmit: function(event) {
				event.preventDefault();
				event.stopPropagation();

				

				let currentObj = this;

				let dateArray = currentObj.newEventData.dateTime.split('.');
				let dateTime = new Date(dateArray[2], dateArray[1]-1, dateArray[0]);

				let newEvent = {
					id: 'new',
					start:  {
						dateTime: dateTime
					},
					location: currentObj.newEventData.address,
					extendedProperties: {
						private: {
							type: currentObj.newEventData.type
						}
					},
					description: currentObj.newEventData.notes
				};

				//currentObj.calendar_events.items.push(newEvent);
				
				let formData = new FormData();
				formData.append('calendar_id', currentObj.calendar_id);
				formData.append('new_event_datetime', currentObj.newEventData.dateTime);
				formData.append('new_event_address', currentObj.newEventData.address);
				formData.append('new_event_type', currentObj.newEventData.type);
				formData.append('new_event_notes', currentObj.newEventData.notes);

				axios.interceptors.request.use(function (config) {
				    // Do something before request is sent
				    currentObj.formRequestProcess = true;
				    return config;
				}, function (error) {
				    // Do something with request error
				    return Promise.reject(error);
				});

				let url = '/calendar-new-event'

				axios.post(url, formData)
				.then(function(response) {

					console.log(currentObj.calendar.events);

					if (response.data.code == 1) {
						currentObj.requestSuccess = response.data.data.message;
						currentObj.calendar.events.push(response.data.data.event);
						
						// Reset New event form
						currentObj.newEventData.dateTime = '';
						currentObj.newEventData.address = '';
						currentObj.newEventData.type = '';
						currentObj.newEventData.notes = '';
					
						setTimeout(function() {
							currentObj.requestSuccess = false;
						}, 2000);
					} else {
						currentObj.requestDanger = 'Request Error';
					}
				})
				.catch(function (error) {

					if (error.response && error.response.status == 422) {
						currentObj.requestDanger = error.response.data.message;
						form.classList.add('was-validated');
					} else {
						currentObj.requestDanger = 'Request Error';
					}

				})
				.then(function() {
					currentObj.formRequestProcess = false;
				});

				
			},
		},


		filters: {
			capitalize: function (value) {
				if (!value) return ''
					value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			},

			formatDate: function(value) {
				let date = new Date(value);
				let month = parseInt(date.getMonth()+1) < 10 ? '0'+(date.getMonth()+1) : (date.getMonth()+1);

				let day = date.getDate() >= 10 ? date.getDate() : '0'+date.getDate();
				return day+'.'+month+'.'+date.getFullYear();
			}

		}

	}
</script>