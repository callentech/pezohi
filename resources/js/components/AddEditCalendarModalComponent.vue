
<!-- AddCalendarModalComponent -->

<template>

	<div class="component-wrapper">

		<!-- Edit Calendar modal -->
        <div v-if="showEditCalendarModal">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-wrapper">

                        <div id="editCalendarModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title w-100 text-center">Edit Calendar Data</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="hideEditCalendarModalAction">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <form id="editCalendarForm" class="needs-validation" method="POST" @submit="editCalendarSubmit" novalidate>

                                            <input type="hidden" name="_token" :value="csrf_token">
                                            <input type="hidden" name="calendar_id" :value="current_calendar.id">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Calendar Name</label>
                                                    <input type="text" name="calendar_name" class="form-control" v-model="current_calendar.name" required :disabled="formRequestProcess">
                                                    <div class="invalid-feedback">Please provide a valid Calendar Name.</div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Owner's Email Address</label>
                                                    <input type="email" name="owner_email_address" class="form-control" v-model="owner_email_address" readonly>
                                                    <div class="invalid-feedback">Please provide a valid Email Address.</div>

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" disabled checked>
                                                        <label class="form-check-label">Owned by me</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <label>Events: {{ current_calendar.events.length }}</label>
                                                <div class="card col-md-12">
                                                    <div class="card-body">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Date</th>
                                                                    <th scope="col">Time</th>
                                                                    <th scope="col">Address</th>
                                                                    <th scope="col">Event type</th>
                                                                    <th scope="col">Notes</th>
                                                                    <th scope="col" class="actions"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="event in current_calendar.events" :data-id="event.id">
                                                                    <td data-val="startDate">{{ event.started_at|formatDate }}</td>
                                                                    <td data-val="startTime">5:30 PM - 6:30 PM</td>
                                                                    <td data-val="location">
                                                                        <a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a>
                                                                    </td>
                                                                    <td data-val="type">{{ event.type|capitalize }}</td>
                                                                    <td data-val="description">
                                                                        <a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="far fa-trash-alt"></i></button>
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'" title="More"><i class="fas fa-ellipsis-h"></i></button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">
                                                            <div class="row">
                                                                <div class="input-group input-group-sm mb-3 col-md-2">
                                                                    <date-picker v-model="newEventData.dateTime" :config="dateOptions" name="new-event-datetime"></date-picker>
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                                    </div>
                                                                </div>

                                                                <div class="input-group input-group-sm mb-3 col-md-2">
                                                                    <input type="text" class="form-control" placeholder="5:30 PM - 6:30 PM">
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <input type="text" v-model="newEventData.location" name="new-event-address" class="form-control form-control-sm" placeholder="Location" required>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <select v-model="newEventData.type" name="new-event-type" class="form-control form-control-sm" required>
                                                                        <option value="" disabled selected>Select type</option>
                                                                        <option value="game">Game</option>
                                                                        <option value="practice">Practice</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <input type="text" v-model="newEventData.description" class="form-control form-control-sm" placeholder="e.g. Instructions">
                                                                </div>
                                                                <div class="col-md-2 text-right">
                                                                    <button class="btn btn-primary btn-sm" @click="addNewEvent" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-check"></i></button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <button class="btn btn-outline-primary btn-sm" @click="showAddEventForm" :disabled="formRequestProcess">+ Add Event</button>
                                                </div>

                                                <div class="col-md-8">
                                                    <transition name="fade">

                                                        <div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                                                            <strong>Success!</strong> {{ requestSuccess }}
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            <strong>Error!</strong> {{ requestDanger }}
                                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                    </transition>
                                                </div>
                                            </div>

                                        </form>
                                    </div>

                                    <div class="modal-footer">
                                        <button v-if="!formRequestProcess" type="submit" form="editCalendarForm" class="btn btn-primary">Save</button>
                                        <button v-else class="btn btn-primary" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                        </button>

                                        <button type="button" class="btn btn-secondary" @click="hideEditCalendarModalAction">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </transition>
        </div>

        <!--
        <div class="modal fade" id="eeditCalendarModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="editCalendarModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-xl">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title w-100 text-center">{{ modal_title }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">

						<form id="editCalendarForm" class="needs-validation" :action="form_action" method="POST" @submit="editCalendarSubmit" novalidate>

							<input type="hidden" name="_token" :value="csrf_token">
							<input type="hidden" name="calendar_id" :value="calendar_id">

							<div class="form-row">

								<div class="form-group col-md-6">
									<label for="calendar_name">Calendar Name</label>
									<input type="text" name="calendar_name" class="form-control" id="calendar_name" v-model="calendar_name" required :disabled="formRequestProcess">
									<div class="invalid-feedback">Please provide a valid Calendar Name.</div>
								</div>

								<div class="form-group col-md-6">
									<label for="owner_email_address">Owner's Email Address</label>
									<input type="email" name="owner_email_address" class="form-control" v-model="owner_email_address" readonly>
									<div class="invalid-feedback">Please provide a valid Email Address.</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" id="owned_by_me_checkbox" disabled checked>
										<label class="form-check-label" for="owned_by_me_checkbox">Owned by me</label>
									</div>
								</div>

							</div>

							<div class="form-row">
								<label>Events
									<span v-if="calendar_events.items">({{ calendar_events.items.length }})</span>
									<span v-else>(0)</span>
								</label>

								<div id="calendarData" class="card col-md-12">

								<div class="card-body">

									<table id="calendarDataTable" class="table table-sm">

										<thead>
											<tr>
												<th scope="col">Date</th>
												<th scope="col">Time</th>
												<th scope="col">Address</th>
												<th scope="col">Event type</th>
												<th scope="col">Notes</th>
												<th class="actions" scope="col"></th>
											</tr>
										</thead>

										<tbody>

											<tr v-for="event in calendar_events.items" :data-id="event.id">
												<th scope="row" data-val="startDate">{{ event.start.dateTime|formatDate('MMMM D, YYYY') }}</th>
												<td data-val="startTime">5:30 PM - 6:30 PM</td>
												<td data-val="location">{{ event.location }}</td>
												<td data-val="type">
													<span  v-if="typeof event.extendedProperties !== 'undefined' && typeof event.extendedProperties.private.type !== 'undefined'">
														{{event.extendedProperties.private.type | capitalize}}
													</span>
												</td>
												<td data-val="description">
													{{ event.description }}
												</td>
												<td class="text-right">
													<button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-pencil-alt"></i></button>
													<button class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt"></i></button>
													<button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-ellipsis-h"></i></button>
												</td>
											</tr>

										</tbody>
									</table>

								</div>

						  		<transition name="fade">
							  		<div class="card-footer" v-if="showNewEventDataForm">
							    		<div class="row">

						    				<div class="input-group input-group-sm mb-3 col-md-2">

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
							    				<button class="btn btn-primary btn-sm" @click="addEditCalendarEvent" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-check"></i></button>
							    				<button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-times"></i></button>
							    			</div>
						    			</div>
							  		</div>
						  		</transition>

							  </div>

							</div>

							<div class="row">
								<div class="col-md-4">
									<button class="btn btn-outline-primary btn-sm" @click="showAddEventForm" :disabled="formRequestProcess">+ Add Event</button>
								</div>

								<div class="col-md-8">
									<transition name="fade">

										<div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
											<strong>Success!</strong> {{ requestSuccess }}
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>

										<div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
											<strong>Error!</strong> {{ requestDanger }}
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										    	<span aria-hidden="true">&times;</span>
										    </button>
										</div>

									</transition>
								</div>
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>

						<button v-if="!formRequestProcess" type="submit" form="editCalendarForm" class="btn btn-primary">Save</button>

						<button v-else class="btn btn-primary" type="button" disabled>
							<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
							Loading...
						</button>

					</div>
				</div>
			</div>
		</div>
        -->
        <!-- END Edit Calendar modal -->

		<!-- Add Calendar modal -->
		<div v-if="showAddCalendarModal">
			<transition name="modal">
				<div class="modal-mask">
	        		<div class="modal-wrapper">

	        			<div id="addCalendarModal" tabindex="-1" role="dialog">
				  			<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
				    			<div class="modal-content">

				      				<div class="modal-header">
				       	 				<h5 class="modal-title w-100 text-center">Add New calendar</h5>
								        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="hideAddCalendarModalAction">
								          <span aria-hidden="true">&times;</span>
								        </button>
								    </div>

								    <div class="modal-body">
								    	<form id="addCalendarForm" class="needs-validation" action="/new-calendar" method="POST" @submit="addCalendarSubmit" novalidate>

											<input type="hidden" name="_token" :value="csrf_token">

											<div class="form-row">
												<div class="form-group col-md-6">
													<label>Calendar Name</label>
													<input type="text" name="calendar_name" class="form-control" v-model="calendar_name" required :disabled="formRequestProcess">
													<div class="invalid-feedback">Please provide a valid Calendar Name.</div>
												</div>
												<div class="form-group col-md-6">
													<label>Owner's Email Address</label>
													<input type="email" name="owner_email_address" class="form-control" v-model="owner_email_address" readonly>
													<div class="invalid-feedback">Please provide a valid Email Address.</div>

													<div class="form-check form-check-inline">
														<input class="form-check-input" type="checkbox" disabled checked>
														<label class="form-check-label">Owned by me</label>
													</div>
												</div>
											</div>

											<div class="form-row">
												<label>
													Events
													<span v-if="calendar_events.items">({{ calendar_events.items.length }})</span>
													<span v-else>(0)</span>
												</label>

												<div class="card col-md-12">
                                                    <div class="card-body">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Date</th>
                                                                    <th scope="col">Time</th>
                                                                    <th scope="col">Address</th>
                                                                    <th scope="col">Event type</th>
                                                                    <th scope="col">Notes</th>
                                                                    <th class="actions" scope="col"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                <tr v-for="event in calendar_events.items" :data-id="event.id">
                                                                    <th scope="row" data-val="startDate">{{ event.start.dateTime|formatDate('MMMM D, YYYY') }}</th>
                                                                    <td data-val="startTime">5:30 PM - 6:30 PM</td>
                                                                    <td data-val="location">{{ event.location }}</td>
                                                                    <td data-val="type">
                                                                        <span  v-if="typeof event.extendedProperties !== 'undefined' && typeof event.extendedProperties.private.type !== 'undefined'">
                                                                            {{event.extendedProperties.private.type | capitalize}}
                                                                        </span>
                                                                    </td>
                                                                    <td data-val="description">
                                                                        {{ event.description }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-pencil-alt"></i></button>
                                                                        <button class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt"></i></button>
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-ellipsis-h"></i></button>
                                                                    </td>
                                                                </tr>

                                                            </tbody>
                                                        </table>

                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">
                                                            <div class="row">

                                                                <div class="input-group input-group-sm mb-3 col-md-2">
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
                                                                    <button class="btn btn-primary btn-sm" @click="addNewCalendarEvent" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-check"></i></button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>

                                                </div>

											</div>

											<div class="row">
												<div class="col-md-4">
													<button class="btn btn-outline-primary btn-sm" @click="showAddEventForm" :disabled="formRequestProcess">+ Add Event</button>
												</div>

												<div class="col-md-8">
													<transition name="fade">

														<div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
															<strong>Success!</strong> {{ requestSuccess }}
															<button type="button" class="close" data-dismiss="alert" aria-label="Close">
																<span aria-hidden="true">&times;</span>
															</button>
														</div>

														<div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
															<strong>Error!</strong> {{ requestDanger }}
															<button type="button" class="close" data-dismiss="alert" aria-label="Close">
														    	<span aria-hidden="true">&times;</span>
														    </button>
														</div>

													</transition>
												</div>
											</div>

										</form>
								    </div>
								    <div class="modal-footer">
								    	<button v-if="!formRequestProcess" type="submit" form="addCalendarForm" class="btn btn-primary">Save</button>
								    	<button v-else class="btn btn-primary" type="button" disabled>
											<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
											Loading...
										</button>

										<button type="button" class="btn btn-secondary" @click="hideAddCalendarModalAction">Close</button>
								    </div>
				    			</div>
				  			</div>
						</div>
	        		</div>
	      		</div>
			</transition>
		</div>

		<!--
		<div class="modal fade" id="addCalendarModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="addCalendarModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-xl">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title w-100 text-center">{{ modal_title }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">

						<form id="addNewCalendarForm" class="needs-validation" :action="form_action" method="POST" @submit="addNewCalendarSubmit" novalidate>

							<input type="hidden" name="_token" :value="csrf_token">


							<div class="form-row">

								<div class="form-group col-md-6">
									<label for="calendar_name">Calendar Name</label>
									<input type="text" name="calendar_name" class="form-control" id="calendar_name" v-model="calendar_name" required :disabled="formRequestProcess">
									<div class="invalid-feedback">Please provide a valid Calendar Name.</div>
								</div>

								<div class="form-group col-md-6">
									<label for="owner_email_address">Owner's Email Address</label>
									<input type="email" name="owner_email_address" class="form-control" v-model="owner_email_address" readonly>
									<div class="invalid-feedback">Please provide a valid Email Address.</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" id="owned_by_me_checkbox" disabled checked>
										<label class="form-check-label" for="owned_by_me_checkbox">Owned by me</label>
									</div>
								</div>

							</div>

							<div class="form-row">
								<label>
									Events
									<span v-if="calendar_events.items">({{ calendar_events.items.length }})</span>
									<span v-else>(0)</span>
								</label>

								<div id="calendarData" class="card col-md-12">

								<div class="card-body">

									<table id="calendarDataTable" class="table table-sm">

										<thead>
											<tr>
												<th scope="col">Date</th>
												<th scope="col">Time</th>
												<th scope="col">Address</th>
												<th scope="col">Event type</th>
												<th scope="col">Notes</th>
												<th class="actions" scope="col"></th>
											</tr>
										</thead>

										<tbody>

											<tr v-for="event in calendar_events.items" :data-id="event.id">
												<th scope="row" data-val="startDate">{{ event.start.dateTime|formatDate('MMMM D, YYYY') }}</th>
												<td data-val="startTime">5:30 PM - 6:30 PM</td>
												<td data-val="location">{{ event.location }}</td>
												<td data-val="type">
													<span  v-if="typeof event.extendedProperties !== 'undefined' && typeof event.extendedProperties.private.type !== 'undefined'">
														{{event.extendedProperties.private.type | capitalize}}
													</span>
												</td>
												<td data-val="description">
													{{ event.description }}
												</td>
												<td class="text-right">
													<button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-pencil-alt"></i></button>
													<button class="btn btn-outline-secondary btn-sm"><i class="far fa-trash-alt"></i></button>
													<button class="btn btn-outline-secondary btn-sm" :disabled="event.id == 'new'"><i class="fas fa-ellipsis-h"></i></button>
												</td>
											</tr>

										</tbody>
									</table>

								</div>

						  		<transition name="fade">
							  		<div class="card-footer" v-if="showNewEventDataForm">
							    		<div class="row">

						    				<div class="input-group input-group-sm mb-3 col-md-2">

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
							    				<button class="btn btn-primary btn-sm" @click="addNewCalendarEvent" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-check"></i></button>
							    				<button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-times"></i></button>
							    			</div>
						    			</div>
							  		</div>
						  		</transition>

							  </div>

							</div>

							<div class="row">
								<div class="col-md-4">
									<button class="btn btn-outline-primary btn-sm" @click="showAddEventForm" :disabled="formRequestProcess">+ Add Event</button>
								</div>

								<div class="col-md-8">
									<transition name="fade">

										<div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
											<strong>Success!</strong> {{ requestSuccess }}
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>

										<div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
											<strong>Error!</strong> {{ requestDanger }}
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										    	<span aria-hidden="true">&times;</span>
										    </button>
										</div>

									</transition>
								</div>
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>

						<button v-if="!formRequestProcess" type="submit" form="addNewCalendarForm" class="btn btn-primary">Save</button>

						<button v-else class="btn btn-primary" type="button" disabled>
							<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
							Loading...
						</button>

					</div>
				</div>
			</div>
		</div>
	-->
		<!-- END Add Calendar modal -->

	</div>

</template>

<script>

	// import 'bootstrap/dist/css/bootstrap.css';

	import datePicker from 'vue-bootstrap-datetimepicker';
	import dateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

	export default {

		//props:['form_action', 'csrf_token', 'modal_title'],
		//props:['modal_data'],

		data() {
			return {
                owner_email_address: '',
                showAddCalendarModal: false,
                showEditCalendarModal: false,
                formRequestProcess: false,
                showNewEventDataForm: false,
                requestSuccess: false,
                requestDanger: false,
                csrf_token: null,
                edit_calendar_id: null,
                current_calendar: null,

                dateOptions: {
                    format: 'M/DD/YYYY',
                    useCurrent: true
                },
                newEventData: {
                    dateTime: '2/10/2020',
                    location: 'Location',
                    type: 'game',
                    description: 'Description'
                },

                /*
				modal_title: '',
				csrf_token: '',
				owner_email_address: '',
				calendar_name: '',
				calendar_id: '',
				calendar_events: [],
				form_action: '',









				//date: new Date(),
				dateOptions: {
					format: 'DD.MM.YYYY',
					useCurrent: true
				},

				showAddCalendarModal: false

                 */
			}
		},

		created() {
			this.$root.$refs.addEditCalendarModal = this;
		},

		components: {
			datePicker,
			dateRangePicker
		},

		computed:  {
			newEventDataValid() {
				return !(
					this.newEventData.location === '' || this.newEventData.dateTime === ''
					|| this.newEventData.type === '' || this.newEventData.description === ''
                );
			}
		},

		methods: {

			showAddCalendarModalAction: function() {
				this.calendar_name = '';
				this.calendar_events = [];
				this.showAddCalendarModal = true;
			},
            hideAddCalendarModalAction: function() {
                this.requestDanger = false;
                this.requestSuccess = false;
                this.calendar_name = '';
                this.calendar_events = [];
                this.showAddCalendarModal = false;
            },
            addCalendarSubmit: function(event) {
                event.preventDefault();
                event.stopPropagation();

                let currentObj = this;
                let form = document.getElementById('addCalendarForm');
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                // Send request
                axios.interceptors.request.use(function (config) {
                    // Do something before request is sent
                    currentObj.formRequestProcess = true;
                    return config;
                }, function (error) {
                    // Do something with request error
                    return Promise.reject(error);
                });

                let formData = new FormData(form);
                axios.post('/new-calendar', formData)
                .then(function(response) {
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        setTimeout(function() {
                            currentObj.requestSuccess = false;
                            currentObj.showAddCalendarModal = false;

                            currentObj.owner_email_address = '';
                            currentObj.calendar_name = '';
                            currentObj.calendar_events = [];

                            location.reload();
                        }, 2000);
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function(response) {
                    currentObj.requestDanger = 'Request Error';
                })
                .then(function(){
                    currentObj.formRequestProcess = false;
                });
            },

            showEditCalendarModalAction: function(id) {
                let currentObj = this;
                axios.get('/get-calendar-data?calendar_id='+id)
                .then(function(response){
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 1) {
                        currentObj.current_calendar = response.data.data.calendarData;
                        currentObj.showEditCalendarModal = true;
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function(response) {
                    currentObj.requestDanger = 'Request Error';
                })
                .then(function() {

                });
            },
            hideEditCalendarModalAction() {
                this.requestDanger = false;
                this.requestSuccess = false;
                this.calendar_name = '';
                this.calendar_events = [];
                this.showEditCalendarModal = false;
            },


            editCalendarSubmit: function(event) {
                event.preventDefault();
                event.stopPropagation();

                let currentObj = this;
                let form = document.getElementById('editCalendarForm');
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                // Send request
                axios.interceptors.request.use(function (config) {
                    // Do something before request is sent
                    currentObj.formRequestProcess = true;
                    return config;
                }, function (error) {
                    // Do something with request error
                    return Promise.reject(error);
                });

                let formData = new FormData(form);
                formData.append('events', JSON.stringify(currentObj.current_calendar.events));
                axios.post('/edit-calendar', formData)
                .then(function(response) {
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 404) {
                        currentObj.requestDanger = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        setTimeout(function() {
                            currentObj.requestSuccess = false;
                            currentObj.showEditCalendarModal = false;

                            currentObj.owner_email_address = '';
                            currentObj.calendar_name = '';
                            currentObj.calendar_events = [];

                            location.reload();
                        }, 2000);
                    } else {
                        currentObj.requestDanger = 'Request Error';
                    }
                })
                .catch(function(response) {
                    currentObj.requestDanger = 'Request Error';
                })
                .then(function(){
                    currentObj.formRequestProcess = false;
                });
            },

            showAddEventForm: function(event) {
                event.preventDefault();
                this.showNewEventDataForm = true;
            },
            addNewEvent: function(event) {
                event.preventDefault();
                let currentObj = this;
                //let dateTime = new Date(currentObj.newEventData.dateTime);


                let newEvent = {
                    id: 'new',
                    //started_at: dateTime,
                    started_at: currentObj.newEventData.dateTime,
                    location: currentObj.newEventData.location,
                    type: currentObj.newEventData.type,
                    description: currentObj.newEventData.description
                };
                currentObj.current_calendar.events.push(newEvent);
                currentObj.newEventData =  {
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
            },











			showDuplicateCalendarModal: function(id) {

				// let currentObj = this;
				// axios.post('/calendar-get-data', { calendar_id: id })
				// .then(function (response) {
				// 	currentObj.modal_title = 'Duplicate Calendar';
                //
				// 	if (response.data.code == 1) {
				// 		currentObj.form_action = '/calendar-new';
				// 		currentObj.calendar_name = response.data.data.calendarData.summary;
				// 		currentObj.calendar_events = response.data.data.calendarEvents;
				// 	} else {
				// 		currentObj.requestDanger = 'Error Request';
				// 	}
				// 	jQuery('#addCalendarModal').modal('show');
				// })
				// .catch(function (error) {
				// 	currentObj.requestDanger = 'Error Request';
				// })
				// .then(function() {
                //
				// });

			},



			hideAddEventForm: function(event) {
				event.preventDefault();

				this.newEventData.dateTime = null;
				this.newEventData.address = null;
				this.newEventData.type = 'none';
				this.newEventData.notes = null;
				this.showNewEventDataForm = false;
			},

			// addCalendarResetForm: function() {
			// 	this.owner_email_address = '';
			// 	this.calendar_name = '';
			// 	this.calendar_events = [];
			// },
/*
			editCalendarSubmit: function(event) {

				// event.preventDefault();
				// event.stopPropagation();
                //
				// this.requestSuccess = false;
				// this.requestDanger = false;
                //
				// let currentObj = this;
				// let form = event.target;
                //
				// if (form.checkValidity() === false) {
				// 	form.classList.add('was-validated');
				// } else {
                //
				// 	let url = event.target.action;
                //
				// 	let formData = new FormData(form);
				// 	formData.append('events', JSON.stringify(currentObj.calendar_events.items));
                //
				// 	axios.interceptors.request.use(function (config) {
				// 	    // Do something before request is sent
				// 	    currentObj.formRequestProcess = true;
				// 	    return config;
				// 	}, function (error) {
				// 	    // Do something with request error
				// 	    return Promise.reject(error);
				// 	});
                //
				// 	axios.post(url, formData)
				// 	.then(function(response) {
				// 		if (response.data.code == 1) {
				// 			currentObj.requestSuccess = response.data.data.message;
				// 			setTimeout(function() {
				// 				currentObj.requestSuccess = false;
				// 				jQuery('#addEditCalendarModal').modal('hide');
				// 				location.reload();
				// 			}, 2000);
				// 		} else {
				// 			currentObj.requestDanger = 'Request Error';
				// 		}
				// 	})
				// 	.catch(function (error) {
                //
				// 		if (error.response && error.response.status == 422) {
				// 			currentObj.requestDanger = error.response.data.message;
				// 			form.classList.add('was-validated');
				// 		} else {
				// 			currentObj.requestDanger = 'Request Error';
				// 		}
                //
				// 	})
				// 	.then(function() {
				// 		currentObj.formRequestProcess = false;
				// 	});
                //
				// }

			},
*/
			addNewCalendarSubmit: function(event) {
				// event.preventDefault();
				// event.stopPropagation();
				// console.log('addNewCalendarSubmit');
                //
				// let currentObj = this;
				// let form = event.target;
                //
				// if (form.checkValidity() === false) {
				// 	form.classList.add('was-validated');
				// } else {
                //
				// 	let url = event.target.action;
                //
				// 	let formData = new FormData(form);
                //
				// 	console.log(currentObj.calendar_events.items);
                //
				// 	formData.append('events', JSON.stringify(currentObj.calendar_events.items));
                //
				// 	axios.interceptors.request.use(function (config) {
				// 	    // Do something before request is sent
				// 	    currentObj.formRequestProcess = true;
				// 	    return config;
				// 	}, function (error) {
				// 	    // Do something with request error
				// 	    return Promise.reject(error);
				// 	});
                //
				// 	axios.post(url, formData)
				// 	.then(function(response) {
				// 		if (response.data.code == 1) {
				// 			currentObj.requestSuccess = response.data.data.message;
                //
				// 			setTimeout(function() {
				// 				currentObj.requestSuccess = false;
				// 				jQuery('#addCalendarModal').modal('hide');
				// 				currentObj.addCalendarResetForm();
				// 				location.reload();
				// 			}, 2000);
				// 		} else {
				// 			currentObj.requestDanger = 'Request Error';
				// 		}
				// 	})
				// 	.catch(function (error) {
                //
				// 		if (error.response && error.response.status == 422) {
				// 			currentObj.requestDanger = error.response.data.message;
				// 			form.classList.add('was-validated');
				// 		} else {
				// 			currentObj.requestDanger = 'Request Error';
				// 		}
                //
				// 	})
				// 	.then(function() {
				// 		currentObj.formRequestProcess = false;
				// 	});


			},
            //
			// addEditCalendarEvent: function(event) {
			// 	// event.preventDefault();
			// 	// event.stopPropagation();
            //     //
			// 	// let currentObj = this;
            //     //
			// 	// let dateArray = currentObj.newEventData.dateTime.split('.');
			// 	// let dateTime = new Date(dateArray[2], dateArray[1]-1, dateArray[0]);
            //     //
			// 	// let newEvent = {
			// 	// 	id: 'new',
			// 	// 	start:  {
			// 	// 		dateTime: dateTime
			// 	// 	},
			// 	// 	location: currentObj.newEventData.address,
			// 	// 	extendedProperties: {
			// 	// 		private: {
			// 	// 			type: currentObj.newEventData.type
			// 	// 		}
			// 	// 	},
			// 	// 	description: currentObj.newEventData.notes
			// 	// };
            //     //
			// 	// currentObj.calendar_events.items.push(newEvent);
            //     //
			// 	// // Reset add event form
			// 	// currentObj.newEventData.dateTime = '';
			// 	// currentObj.newEventData.address = '';
			// 	// currentObj.newEventData.type = '';
			// 	// currentObj.newEventData.notes = '';
			// },
            //
			// addNewCalendarEvent: function(event) {
			// 	// event.preventDefault();
			// 	// event.stopPropagation();
			// 	// console.log('addNewCalendarEvent');
            //     //
			// 	// let currentObj = this;
            //     //
			// 	// let dateArray = currentObj.newEventData.dateTime.split('.');
			// 	// let dateTime = new Date(dateArray[2], dateArray[1]-1, dateArray[0]);
            //     //
			// 	// let newEvent = {
			// 	// 	id: 'new',
			// 	// 	start:  {
			// 	// 		dateTime: dateTime
			// 	// 	},
			// 	// 	location: currentObj.newEventData.address,
			// 	// 	extendedProperties: {
			// 	// 		private: {
			// 	// 			type: currentObj.newEventData.type
			// 	// 		}
			// 	// 	},
			// 	// 	description: currentObj.newEventData.notes
			// 	// };
            //     //
			// 	// let items = currentObj.calendar_events.items ? currentObj.calendar_events.items : [];
			// 	// currentObj.calendar_events.items = items;
			// 	// currentObj.calendar_events.items.push(newEvent);
            //     //
			// 	// // Reset add event form
			// 	// currentObj.newEventData.dateTime = '';
			// 	// currentObj.newEventData.address = '';
			// 	// currentObj.newEventData.type = '';
			// 	// currentObj.newEventData.notes = '';
			// }
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
				//let month = parseInt(date.getMonth()+1) < 10 ? '0'+(date.getMonth()+1) : (date.getMonth()+1);
				let day = date.getDate() >= 10 ? date.getDate() : '0'+date.getDate();
				//return day+'.'+month+'.'+date.getFullYear();
                return (date.getMonth()+1)+'/'+day+'/'+date.getFullYear();
			},

            sliceString: function(value) {
                let sliced = value.slice(0,10);
                if (sliced.length < value.length) {
                    sliced += '...';
                }
                return sliced;
            }


		}
	}

</script>


