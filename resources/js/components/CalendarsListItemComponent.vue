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
	                    <i class="fas fa-angle-down"></i>
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
	                    <button type="button"class="btn btn-outline-primary btn-sm pull-right" name="button"><i class="fa fa-plus"></i> Add event</button>
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
								    		<div class="row">

							    				<div class="input-group input-group-sm mb-3 col-md-2">
													<!-- <input type="date" class="form-control" name="event-date" data-date-format="DD MMMM YYYY"> -->

													<!-- <date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new-event-datetime"></date-picker>
 -->
													<div class="input-group-append">
														<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
													</div>
												</div>

												

								    			<div class="input-group input-group-sm mb-3 col-md-2">
													<input type="text" class="form-control" placeholder="5:30 PM - 6:30 PM">
												</div>


								    			<div class="col-md-2">
								    				<input type="text" name="new-event-address" class="form-control form-control-sm">
								    			</div>
								    			<div class="col-md-2">
								    				<select name="new-event-type" class="form-control form-control-sm">
													    <option value="none" disabled selected>Select One</option>
													    <option value="game">Game</option>
													    <option value="practice">Practice</option>
												    </select>
									    		</div>
								    			<div class="col-md-2">
								    				<input type="text"  class="form-control form-control-sm" placeholder="e.g. Instructions">
								    			</div>
								    			<div class="col-md-2 text-right">
								    				
								    				<button class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
								    				<button type="button" class="btn btn-outline-secondary btn-sm" ><i class="fas fa-times"></i></button>
								    			</div>
							    			</div>
								  		</div>
							  		</transition>

	            </div>

		    	<!--
	            <div class="row align-items-center">
	                <div class="col-lg-6">
	                    1-5 of {{ calendar.events.length }} <i class="fa fa-angle-right"></i> <a href="#" class="btn btn-link">View all</a>
	                </div>
	                <div class="col-lg-6 text-right">
	                    <button type="button"class="btn btn-primary pull-right" name="button"><i class="fa fa-plus"></i> Add event</button>
	                </div>
	            </div>
	            <table class="table">
	                <thead>
	                    <tr>
	                        <th>
	                            <a href="#">Date</a>
	                        </th>
	                        <th>
	                            <a href="#">Time</a>
	                        </th>
	                        <th>
	                            <a href="#">Address</a>
	                        </th>
	                        <th>
	                            <a href="#">Event</a>
	                        </th>
	                        <th>
	                            <a href="#">Notes</a>
	                        </th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <tr v-for="event in calendar.events">
	                        <td>12/04/21</td>
	                        <td>5:20 PM - 7:20 PM</td>
	                        <td>290 N. Hilldale</td>
	                        <td>Game</td>
	                        <td>Take snacks for afterparty</td>
	                        <td class="text-right">
	                            <a href="#" class="btn btn-light"><i class="fa fa-edit"></i></a>
	                            <a href="#" class="btn btn-light"><i class="fa fa-ellipsis-v "></i></a>
	                        </td>
	                    </tr>
	                </tbody>
	            </table>
	        -->
	        </div>
	    </transition>

	</div>

</template>

<script>

    export default {

        props:['calendar'],

        data() {
            return {
            	showBody: false,
            	showNewEventDataForm: true
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
				});
				this.showBody = !this.showBody;
            }

        },

        mounted() {

        }
    }
</script>