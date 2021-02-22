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
			                	<a href=""><i class="far fa-trash-alt"></i> Delete</a>
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

	            	
				  		<div class="card-footer" v-if="showNewEventDataForm">

				  			<form id="addCalendarEventForm" class="needs-validation" action="new_event_form_action" method="POST" submit="addEventSubmit" novalidate>

				  				
				  				<input type="hidden" name="calendar_id" :value="calendar.id">

					    		<div class="row">

				    				<!-- <div class="input-group input-group-sm mb-3 col-md-2">
										<date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new_event_datetime" :disabled="inputDisabled" placeholder="dd.mm.YYYY" required></date-picker>
										<div class="input-group-append">
											<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
										</div>
										<div class="invalid-feedback">Please provide a valid date.</div>
									</div>
									

					    			<div class="input-group input-group-sm mb-3 col-md-2">
										<input type="text" class="form-control" placeholder="H:MM PM - H:MM PM" :disabled="inputDisabled" required value="05:20 PM - 10:35 PM">
										<div class="invalid-feedback">Please provide a valid times.</div>
									</div> -->

					    			<div class="col-md-2">
					    				<input type="text" name="new_event_address" class="form-control form-control-sm" placeholder="Address" required>
					    				<div class="invalid-feedback">Please provide a valid address.</div>
					    			</div>

					    			<div class="col-md-2">
					    				<select name="new_event_type" class="form-control form-control-sm" required>
					    					<option value="" disabled selected>Select type</option>
										    <option value="game">Game</option>
										    <option value="practice">Practice</option>
									    </select>
									    <div class="invalid-feedback">Please provide a valid type.</div>
						    		</div>

					    			<div class="col-md-2">
					    				<input type="text" name="new_event_notes" class="form-control form-control-sm" placeholder="e.g. Instructions" required>
					    				<div class="invalid-feedback">Please provide a valid notes.</div>
					    			</div>

					    			<div class="col-md-2 text-right">
					    				<button type="submit" form="addCalendarEventForm" class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
					    				<button class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button> 
					    				<button type="button" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i></button>
					    			</div>
				    			</div>

				    			
									<div class="alert alert-success" role="alert"></div>
									<div class="alert alert-danger" role="alert"></div>
								

			    			</form>
				  		</div>
			  		

	            </div>
			</div>
		</transition>
		<!-- END Calendar details -->
	</div>

</template>

<script>
	export default {

		props:['calendar'],

		data() {
			return {
				showBody: false,
				showCalendarDropdownActions: false,
				showNewEventDataForm: false
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

			showAddEventForm: function() {
				this.showNewEventDataForm = true;
			}
		}

	}
</script>