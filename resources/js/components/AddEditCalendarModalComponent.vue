
<!-- AddCalendarModalComponent -->

<template>
	
	<div class="component-wrapper">


		<div class="modal fade" id="addEditCalendarModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="addCalendarModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-xl">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title w-100 text-center">{{ modal_title }}</h5>
					    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					    	<span aria-hidden="true">&times;</span>
					    </button>
					</div>
					<div class="modal-body">

						

						<form id="addCalendarForm" class="needs-validation" :action="form_action" method="POST" @submit="addEditCalendarSubmit" novalidate>

							<input type="hidden" name="_token" :value="csrf_token">
							<input type="hidden" name="calendar_id" :value="calendar_id">

							<div class="form-row">

						    	<div class="form-group col-md-6">
						      		<label for="calendar_name">Calendar Name</label>
						      		<input type="text" name="calendar_name" class="form-control" id="calendar_name" v-model="calendar_name" required :disabled="inputDisabled">
						      		<div class="invalid-feedback">Please provide a valid Calendar Name.</div>
						    	</div>

						    	<div class="form-group col-md-6">
						      		<label for="owner_email_address">Owner's Email Address</label>
						      		<input type="email" name="owner_email_address" class="form-control" id="owner_email_address" v-model="owner_email_address" equired disabled>
						      		<div class="invalid-feedback">Please provide a valid Email Address.</div>

						      		<div class="form-check form-check-inline">
									  <input class="form-check-input" type="checkbox" id="owned_by_me_checkbox" disabled checked>
									  <label class="form-check-label" for="owned_by_me_checkbox">Owned by me</label>
									</div>
						    	</div>

						  	</div>

						  	<div class="form-row">
						  		<label>Events <span>(0)</span></label>
						  		<div class="card col-md-12">

						  			<div class="card-header row">
									    <div class="col-md-2">Date</div>
									    <div class="col-md-2">Time</div>
									    <div class="col-md-2">Address</div>
									    <div class="col-md-2">Event type</div>
									    <div class="col-md-2">Notes</div>
									    <div class="col-md-2"></div>
									</div>
									

									<div class="card-body">

										<ul class="list-group list-group-flush">

								  			<li v-for="event in calendar_events.items" class="list-group-item">
								  				
								    			<div class="row">
								    				<!-- <div class="col-md-2">9/30/20</div> -->
								    				<div class="col-md-2">{{ event.start.dateTime|formatDate('MMMM D, YYYY')  }}</div>
									    			<!-- <div class="col-md-2">5:30 PM - 6:30 PM</div> -->
									    			<div class="col-md-2">{{ event.startTime }} - {{ event.endTime }}</div>
									    			<!-- <div class="col-md-2">255 Washington St. Westwood, MA</div> -->

									    			<div class="col-md-2">{{ event.location }}</div>
									    			<!-- <div class="col-md-2">Practice</div> -->
									    			<div class="col-md-2">
									    				<div  v-if="typeof event.extendedProperties !== 'undefined' && typeof event.extendedProperties.private.type !== 'undefined'">
															{{event.extendedProperties.private.type | capitalize}}
														</div>
									    			</div>
									    			<div class="col-md-2">{{ event.description }}</div>
									    			<div class="col-md-2">
									    				<button class="btn btn-outline-secondary btn-sm"><i class="fas fa-pencil-alt"></i></button>
									    				<button class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt"></i></button>
									    				<button class="btn btn-outline-secondary btn-sm"><i class="fas fa-ellipsis-h"></i></button>
									    			</div>
								    			</div>
								    		</li>

								  		</ul>

										
									</div>
							  		
							  		<transition name="fade">
								  		<div class="card-footer" v-if="showNewEventDataForm">
								    		<div class="row">

							    				<div class="input-group input-group-sm mb-3 col-md-2">
													<!-- <input type="date" class="form-control" name="event-date" data-date-format="DD MMMM YYYY"> -->

													<date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new-event-datetime"></date-picker>

													<div class="input-group-append">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
												</div>

												

								    			<div class="input-group input-group-sm mb-3 col-md-2">
													<input type="text" class="form-control" placeholder="5:30 PM - 6:30 PM">
												</div>


								    			<div class="col-md-2">
								    				<input type="text" v-model="newEventData.address" name="new-event-address" class="form-control form-control-sm">
								    			</div>
								    			<div class="col-md-2">
								    				<select v-model="newEventData.type" name="new-event-type" class="form-control form-control-sm">
													    <option value="none" disabled selected>Select One</option>
													    <option value="game">Game</option>
													    <option value="practice">Practice</option>
												    </select>
									    		</div>
								    			<div class="col-md-2">
								    				<input type="text" v-model="newEventData.notes" class="form-control form-control-sm" placeholder="e.g. Instructions">
								    			</div>
								    			<div class="col-md-2 text-right">
								    				
								    				<button class="btn btn-primary btn-sm" @click="addEventSubmit" :disabled="!newEventDataValid"><i class="fas fa-check"></i></button>
								    				<button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm"><i class="fas fa-times"></i></button>
								    			</div>
							    			</div>
								  		</div>
							  		</transition>
								</div>

						  	</div>

						  	<div class="row">
								<div class="col-md-12">
									<button class="btn" @click="showAddEventForm">+ Add Event</button>

									<transition name="fade">
										<div v-if="requestSuccess" class="alert alert-success" role="alert">{{ requestSuccess }}</div>
										<div v-if="requestDanger" class="alert alert-danger" role="alert">{{ requestDanger }}</div>
									</transition>
								</div>
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>

						<button v-if="!formRequestProcess" type="submit" form="addCalendarForm" class="btn btn-primary">Save</button>

						<button v-else class="btn btn-primary" type="button" disabled >
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

	//import 'bootstrap/dist/css/bootstrap.css';

	import datePicker from 'vue-bootstrap-datetimepicker';
	import DateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
	
	export default {
		
		//props:['form_action', 'csrf_token', 'modal_title'],
		//props:['modal_data'],

		data() {
			return {

				modal_title: '',
				csrf_token: '',
				owner_email_address: '',
				calendar_name: '',
				calendar_id: '',
				calendar_events: [],
				form_action: '',


				inputDisabled: false,

				formRequestProcess: false,

				requestSuccess: false,
				requestDanger: false,

				showNewEventDataForm: false,


				newEventData: {
					dateTime: '01.02.2021',
					address: 'Address',
					type: 'game',
					notes: 'Notes'
				},

				//date: new Date(),
			    dateOptions: {
			    	format: 'DD.MM.YYYY',
			        useCurrent: true
			    },
			}
		},

		created() {
        	this.$root.$refs.addEditCalendarModal = this;
    	},

		components: {
        	datePicker,
        	DateRangePicker
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


			showAddCalendarModal: function() {
				this.modal_title = 'Add Calendar';
				this.form_action = '/calendar-new';
				
				this.calendar_name = '';
				
				jQuery('#addEditCalendarModal').modal('show');
			},

			showEditCalendarModal: function(id) {
				
				let currentObj = this;
				axios.post('/calendar-get-data', { calendar_id: id })
			
				.then(function (response) {

					console.log(response);

					currentObj.modal_title = 'Edit Calendar';
					currentObj.form_action = '/calendar-edit';
					
					currentObj.calendar_id = id;
					currentObj.calendar_name = response.data.data.calendarData.summary;

					currentObj.calendar_events = response.data.data.calendarEvents;



					jQuery('#addEditCalendarModal').modal('show');

					// if (response.data.code == 1) {
					// 	currentObj.requestSuccess = 'Success create calendar';
					// 	currentObj.addCalendarResetForm();
					// 	setTimeout(function() {
					// 		currentObj.requestSuccess = false;
					// 		jQuery('#addCalendarModal').modal('hide');
					// 		location.reload();
					// 	}, 3000);
					// } else {
					// 	currentObj.requestDanger = 'Error Request';
					// }
				})
				.catch(function (error) {
					console.log(error);
					// if (error.response.status == 422) {
					// 	currentObj.requestDanger = error.response.data.message;
					// 	form.classList.add('was-validated');
					// } else {
					// 	currentObj.requestDanger = 'Error Request';
					// }
				})
				.then(function() {
					//currentObj.formRequestProcess = false;
					// currentObj.inputDisabled = false;
				});

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

			addEventSubmit: function(event) {

			},

			addCalendarResetForm: function() {
				this.owner_email_address = '';
				this.calendar_name = '';
			},

			
			addEditCalendarSubmit: function(event) {

				event.preventDefault();
		        event.stopPropagation();

		        this.requestSuccess = false;
				this.requestDanger = false;


				let currentObj = this;
				let form = event.target;

				if (form.checkValidity() === false) {
					form.classList.add('was-validated');
				} else {

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
					.then(function(response) {

						

						// if (response.data.code == 1) {
						// 	currentObj.requestSuccess = response.data.data.message;

						// 	currentObj.addCalendarResetForm();

						// } else {
						// 	currentObj.requestDanger = 'Request Error';
						// }

					})
					.catch(function (error) {

						if (error && error.response.status == 422) {
							currentObj.requestDanger = error.response.data.message;
							form.classList.add('was-validated');
						} else {
							currentObj.requestDanger = 'Request Error';
						}

					})
					.then(function() {

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

				*/

			}
		},

		mounted() {
			this.csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
			this.owner_email_address = document.querySelector('meta[name="current_user_email"]').getAttribute('content');
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
  				return date.getDate()+'.'+month+'.'+date.getFullYear();
  			}
  
		}
	}

</script>


