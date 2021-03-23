
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
                                                                <tr v-for="(event, index) in current_calendar.events" :data-index="index">
                                                                    <td data-val="startDate" colspan="2">
                                                                        {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                    </td>

                                                                    <td data-val="location">
                                                                        <a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a>
                                                                    </td>
                                                                    <td data-val="type">
                                                                        {{ event.type|capitalize }}
                                                                    </td>
                                                                    <td data-val="description">
                                                                        <a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                        <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="More"><i class="fas fa-ellipsis-h"></i></button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">
                                                            <div class="row">
                                                                <div class="input-group input-group-sm mb-3 col-md-4">
                                                                    <date-range-picker
                                                                        v-model="dateRange",
                                                                        :time-picker="timePicker"
                                                                        :showWeekNumbers="showWeekNumbers"
                                                                        :singleDatePicker="singleDatePicker"
                                                                        :showDropdowns="showDropdowns"
                                                                        :ranges="ranges"
                                                                        :always-show-calendars="showCalendar"
                                                                        :appendToBody="appendToBody"
                                                                        valueType="format"
                                                                    >
                                                                        <!--    header slot-->
                                                                        <div slot="header" slot-scope="header" class="slot">
                                                                            <h3>Select Event date & time</h3>
                                                                        </div>

                                                                        <!--    input slot (new slot syntax)-->
                                                                        <template #input="picker" name="event-date-range">
                                                                            {{ picker.startDate | date }} - {{ picker.endDate | date }}
                                                                        </template>

                                                                        <!--    footer slot-->
                                                                        <div slot="footer" slot-scope="data" class="slot">
                                                                            Selected range : {{data.rangeText}}
                                                                            <div style="margin-left: auto">
                                                                                <a @click="data.clickApply" class="btn btn-primary btn-sm">Set range</a>
                                                                            </div>
                                                                        </div>
                                                                    </date-range-picker>
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
                                                                    <button class="btn btn-primary btn-sm" @click="saveEvent" :disabled="formRequestProcess"><i class="fas fa-check"></i></button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>
                                                </div>

                                            </div>

                                            <div class="row modal-bottom">
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
        <!-- END Edit Calendar modal -->

        <!-- Duplicate calendar modal -->
        <div v-if="showDuplicateCalendarModal">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-wrapper">

                        <div id="duplicateCalendarModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title w-100 text-center">Duplicate Calendar Data</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="hideDuplicateCalendarModalAction">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <form id="duplicateCalendarForm" class="needs-validation" method="POST" @submit="duplicateCalendarSubmit" novalidate>

                                            <input type="hidden" name="_token" :value="csrf_token">

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
                                                            <tr v-for="(event, index) in current_calendar.events" :data-index="index">
                                                                <td data-val="startDate" colspan="2">
                                                                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                </td>
                                                                <td data-val="location">
                                                                    <a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a>
                                                                </td>
                                                                <td data-val="type">
                                                                    {{ event.type|capitalize }}
                                                                </td>
                                                                <td data-val="description">
                                                                    <a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a>
                                                                </td>
                                                                <td class="text-right">
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="More"><i class="fas fa-ellipsis-h"></i></button>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">
                                                            <div class="row">
                                                                <div class="input-group input-group-sm mb-3 col-md-4">
                                                                    <date-range-picker
                                                                        v-model="dateRange",
                                                                        :time-picker="timePicker"
                                                                        :showWeekNumbers="showWeekNumbers"
                                                                        :singleDatePicker="singleDatePicker"
                                                                        :showDropdowns="showDropdowns"
                                                                        :ranges="ranges"
                                                                        :always-show-calendars="showCalendar"
                                                                        :appendToBody="appendToBody"
                                                                        valueType="format"
                                                                    >
                                                                        <!--    header slot-->
                                                                        <div slot="header" slot-scope="header" class="slot">
                                                                            <h3>Select Event date & time</h3>
                                                                        </div>

                                                                        <!--    input slot (new slot syntax)-->
                                                                        <template #input="picker" name="event-date-range">
                                                                            {{ picker.startDate | date }} - {{ picker.endDate | date }}
                                                                        </template>

                                                                        <!--    footer slot-->
                                                                        <div slot="footer" slot-scope="data" class="slot">
                                                                            Selected range : {{data.rangeText}}
                                                                            <div style="margin-left: auto">
                                                                                <a @click="data.clickApply" class="btn btn-primary btn-sm">Set range</a>
                                                                            </div>
                                                                        </div>
                                                                    </date-range-picker>
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
                                                                    <button class="btn btn-primary btn-sm" @click="saveEvent" :disabled="formRequestProcess"><i class="fas fa-check"></i></button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>
                                                </div>

                                            </div>

                                            <div class="row modal-bottom">
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
                                        <button v-if="!formRequestProcess" type="submit" form="duplicateCalendarForm" class="btn btn-primary">Save</button>
                                        <button v-else class="btn btn-primary" type="button" disabled>
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                        </button>

                                        <button type="button" class="btn btn-secondary" @click="hideDuplicateCalendarModalAction">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </transition>
        </div>
        <!-- END Duplicate calendar modal -->

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
													<input type="text" name="calendar_name" class="form-control" v-model="new_calendar.summary" required :disabled="formRequestProcess">
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
                                                <label>Events: {{ new_calendar.events.length }}</label>
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
                                                            <tr v-for="(event, index) in new_calendar.events" :data-index="index">
                                                                <td data-val="startDate" colspan="2">
                                                                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                </td>
                                                                <td data-val="location">
                                                                    <a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a>
                                                                </td>
                                                                <td data-val="type">
                                                                    {{ event.type|capitalize }}
                                                                </td>
                                                                <td data-val="description">
                                                                    <a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a>
                                                                </td>
                                                                <td class="text-right">
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="More"><i class="fas fa-ellipsis-h"></i></button>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">
                                                            <div class="row">


                                                                <div class="input-group input-group-sm mb-3 col-md-4">
                                                                    <date-range-picker
                                                                        v-model="dateRange",
                                                                        :time-picker="timePicker"
                                                                        :showWeekNumbers="showWeekNumbers"
                                                                        :singleDatePicker="singleDatePicker"
                                                                        :showDropdowns="showDropdowns"
                                                                        :ranges="ranges"
                                                                        :always-show-calendars="showCalendar"
                                                                        :appendToBody="appendToBody"
                                                                        valueType="format"
                                                                    >
                                                                        <!--    header slot-->
                                                                        <div slot="header" slot-scope="header" class="slot">
                                                                            <h3>Select Event date & time</h3>
                                                                        </div>

                                                                        <!--    input slot (new slot syntax)-->
                                                                        <template #input="picker" name="event-date-range">
                                                                            {{ picker.startDate | date }} - {{ picker.endDate | date }}
                                                                        </template>

                                                                        <!--    footer slot-->
                                                                        <div slot="footer" slot-scope="data" class="slot">
                                                                            Selected range : {{data.rangeText}}
                                                                            <div style="margin-left: auto">
                                                                                <a @click="data.clickApply" class="btn btn-primary btn-sm">Set range</a>
                                                                            </div>
                                                                        </div>
                                                                    </date-range-picker>
                                                                </div>


                                                                <div class="col-md-2">
                                                                    <input type="text" v-model="newEventData.location" name="new-event-address" class="form-control form-control-sm">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <select v-model="newEventData.type" name="new-event-type" class="form-control form-control-sm">
                                                                        <option value="none" disabled selected>Select One</option>
                                                                        <option value="game">Game</option>
                                                                        <option value="practice">Practice</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <input type="text" v-model="newEventData.description" class="form-control form-control-sm" placeholder="e.g. Instructions">
                                                                </div>
                                                                <div class="col-md-2 text-right">
                                                                    <!-- <button class="btn btn-primary btn-sm" @click="saveNewCalendarEvent" :disabled="!newEventDataValid || formRequestProcess"><i class="fas fa-check"></i></button> -->
                                                                    <button class="btn btn-primary btn-sm" @click="saveNewCalendarEvent" :disabled="formRequestProcess"><i class="fas fa-check"></i></button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="hideAddEventForm" :disabled="formRequestProcess"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition>

                                                </div>

											</div>

											<div class="row modal-bottom">
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
		<!-- END Add Calendar modal -->

	</div>

</template>

<script>

	import datePicker from 'vue-bootstrap-datetimepicker';
	import dateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

	export default {

		data() {
            let startDate = new Date();
            let endDate = new Date();
            endDate.setDate(endDate.getDate());

            return {

                dateRange: { startDate, endDate },
                timePicker: true,
                dateFormat: 'M/DD/YYYY',
                showWeekNumbers: false,
                singleDatePicker: false,

                showDropdowns: false,
                ranges: false,
                showCalendar: true,
                owner_email_address: '',
                showAddCalendarModal: false,
                showEditCalendarModal: false,
                showDuplicateCalendarModal: false,
                formRequestProcess: false,
                showNewEventDataForm: false,
                requestSuccess: false,
                requestDanger: false,
                csrf_token: null,
                edit_calendar_id: null,
                current_calendar: null,
                appendToBody: false,
                new_calendar: {
                    summary: '',
                    events: []
                },

                dateOptions: {
                    format: 'M/DD/YYYY',
                    useCurrent: true
                },
                newEventData: {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                },


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
                this.newEventData = {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
                this.showNewEventDataForm = false;
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
                formData.append('events', JSON.stringify(currentObj.new_calendar.events));
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

                this.newEventData = {
                    index: '',
                        dateTime: '',
                        location: '',
                        type: '',
                        description: ''
                };
                this.showNewEventDataForm = false;
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
            hideAddEventForm: function(event) {
                event.preventDefault();

                this.newEventData =  {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
                this.showNewEventDataForm = false;
            },
            saveEvent: function(event) {
                event.preventDefault();
                let currentObj = this;

                if (currentObj.newEventData.index === '') {
                    console.log(currentObj.dateRange);
                    // Add new Event
                    let newEvent = {
                        id: 'new',
                        started_at: currentObj.dateRange.startDate,
                        ended_at: currentObj.dateRange.endDate,
                        location: currentObj.newEventData.location,
                        type: currentObj.newEventData.type,
                        description: currentObj.newEventData.description
                    };
                    currentObj.current_calendar.events.push(newEvent);
                } else {
                    // Update current event
                    currentObj.current_calendar.events[currentObj.newEventData.index].started_at = currentObj.dateRange.startDate;
                    currentObj.current_calendar.events[currentObj.newEventData.index].ended_at = currentObj.dateRange.endDate;
                    currentObj.current_calendar.events[currentObj.newEventData.index].location = currentObj.newEventData.location;
                    currentObj.current_calendar.events[currentObj.newEventData.index].type = currentObj.newEventData.type;
                    currentObj.current_calendar.events[currentObj.newEventData.index].description = currentObj.newEventData.description;
                }

                currentObj.newEventData =  {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
                this.showNewEventDataForm = false;
            },
            removeEvent: function(index, event) {
                event.preventDefault();
                this.current_calendar.events.splice(index, 1);
            },

            saveNewCalendarEvent: function(event) {
			    event.preventDefault();
                let currentObj = this;
                let newEvent = {
                    id: 'new',
                    started_at: currentObj.dateRange.startDate,
                    ended_at: currentObj.dateRange.endDate,
                    location: currentObj.newEventData.location,
                    type: currentObj.newEventData.type,
                    description: currentObj.newEventData.description
                };
                currentObj.new_calendar.events.push(newEvent);
                currentObj.newEventData =  {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
                this.showNewEventDataForm = false;
            },


            editEvent: function(index, event) {
			    event.preventDefault();
                let currentEvent = this.current_calendar.events[index];
                this.newEventData =  {
                    index: index,
                    dateTime: this.$options.filters.formatDate(currentEvent.started_at),
                    location: currentEvent.location,
                    type: currentEvent.type,
                    description: currentEvent.description
                };
                this.showNewEventDataForm = true;
            },

            showDuplicateCalendarModalAction: function(id) {

                let currentObj = this;
                axios.get('/get-calendar-data?calendar_id='+id)
                .then(function(response){
                    if (response.data.code === 401) {
                        document.location.href="/";
                    } else if (response.data.code === 1) {
                        currentObj.current_calendar = response.data.data.calendarData;
                        currentObj.showDuplicateCalendarModal = true;
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
            hideDuplicateCalendarModalAction: function() {
                this.requestDanger = false;
                this.requestSuccess = false;
                this.calendar_name = '';
                this.calendar_events = [];
                this.showDuplicateCalendarModal = false;
                this.newEventData = {
                    index: '',
                    dateTime: '',
                    location: '',
                    type: '',
                    description: ''
                };
                this.showNewEventDataForm = false;
            },
            duplicateCalendarSubmit: function(event) {
                event.preventDefault();
                event.stopPropagation();

                let currentObj = this;
                let form = document.getElementById('duplicateCalendarForm');
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
                // formData.append('events', JSON.stringify(currentObj.new_calendar.events));
                formData.append('events', JSON.stringify(currentObj.current_calendar.events));
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
            formatTime: function(value) {
                let date = new Date(value);
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12;
                hours = hours ? hours : 12;
                minutes = minutes < 10 ? '0'+minutes : minutes;
                return hours+':'+minutes+' '+ampm;
            },

            sliceString: function(value) {
                let sliced = value.slice(0,10);
                if (sliced.length < value.length) {
                    sliced += '...';
                }
                return sliced;
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
		}
	}

</script>


