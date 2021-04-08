
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
                                                <div class="input-group input-group-sm mb-3 col-5">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Calendar name</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="calendar_name" v-model="current_calendar.name" required :disabled="formRequestProcess">
                                                </div>
                                                <div class="invalid-feedback">Please provide a valid Calendar Name.</div>
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Owner's Email Address</span>
                                                        </div>
                                                        <input type="email" class="form-control" name="owner_email_address" v-model="current_calendar.user.email" required readonly :disabled="formRequestProcess">
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="form-group form-check form-group-sm">
                                                        <input class="form-check-input" type="checkbox" value="" id="ownerByMeCheckbox" checked disabled>
                                                        <label class="form-check-label" for="ownerByMeCheckbox">
                                                            Owned by me
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <label>Events: {{ current_calendar.events.length }}</label>
                                                <div class="card col-md-12">


                                                    <div id="editedCalendarEventsContent" class="card-body">

                                                        <div class="row header">
                                                            <div class="col-3">Date</div>
                                                            <div class="col-2">Address</div>
                                                            <div class="col-2">Event type</div>
                                                            <div class="col-2">Notes</div>
                                                            <div class="col-1">Status</div>
                                                            <div class="col-2 text-right">Actions</div>
                                                        </div>
                                                        <hr>
                                                        <div  class="content">
                                                            <div v-for="(event, index) in current_calendar.events" :data-index="index">

                                                                <div class="row" v-bind:class="{ 'over-status': event.status === 'over', 'cancelled-status': event.status === 'cancelled', 'deleted-status': event.status === 'deleted'  }">
                                                                    <div class="col-3 date">
                                                                        {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                    </div>
                                                                    <div class="col-2">
                                                                        <a href="javascript:void(0)" :title="event.location">
                                                                            {{ event.location|sliceString }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-2">
                                                                        {{ event.type|capitalize }}
                                                                    </div>
                                                                    <div class="col-2">
                                                                        <a href="javascript:void(0)" :title="event.description">
                                                                            {{ event.description|sliceString }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-1">
                                                                        <span v-if="event.status === 'over'" class="badge badge-info event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'confirmed'" class="badge badge-success event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'cancelled'" class="badge badge-warning event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'deleted'" class="badge badge-danger event-status-badge">{{event.status}}</span>
                                                                    </div>

                                                                    <div v-if="event.status === 'over'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'cancelled'" class="col-2 text-right actions">
                                                                        <button v-if="event.current_status" class="btn btn-outline-danger btn-sm" title="Restore" @click="$event.preventDefault(), restoreEditedEventStatus(index)">
                                                                            <i class="fas fa-trash-restore-alt"></i>
                                                                        </button>
                                                                        <button v-else class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'deleted'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-danger btn-sm" title="Restore" @click="$event.preventDefault(), restoreEditedEventStatus(index)">
                                                                            <i class="fas fa-trash-restore-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'duplicated'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Edit" @click="$event.preventDefault(), editEditedEvent(index)">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), removeDuplicatedEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Edit" @click="$event.preventDefault(), editEditedEvent(index)">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Duplicate" @click="$event.preventDefault(), duplicateEditedEvent(index)">
                                                                            <i class="far fa-clone"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Cancel" @click="$event.preventDefault(), markCancelEditedEvent(index)">
                                                                            <i class="fas fa-ban"></i>
                                                                        </button>

                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <!--
                                                    <div class="card-body">
                                                        <table class="table table-sm edited-event-data">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col">Date</th>
                                                                <th scope="col">Address</th>
                                                                <th scope="col">Event type</th>
                                                                <th scope="col">Notes</th>
                                                                <th scope="col">Status</th>
                                                                <th scope="col" class="actions"></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="(event, index) in current_calendar.events" :data-index="index">


                                                                <td data-val="startDate" v-if="event.status === 'cancelled' || event.status === 'over' || moment(event.ended_at).isBefore(new Date())" class="text-muted event-cancelled">
                                                                    {{ event.started_at|formatDate }}
                                                                </td>
                                                                <td data-val="startDate" v-else>
                                                                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                </td>

                                                                <td data-val="location" v-if="event.status === 'cancelled' || event.status === 'over' || moment(event.ended_at).isBefore(new Date())" class="text-muted event-cancelled">
                                                                    {{ event.location|sliceString }}
                                                                </td>
                                                                <td data-val="location" v-else>
                                                                    <a href="javascript:void(0)" title="Details" v-if="event.location != null" @click="showEventDetails($event, event.location, event.description)">
                                                                        {{ event.location|sliceString }} <i class="fas fa-angle-down"></i>
                                                                    </a>
                                                                </td>

                                                                <td data-val="type" v-if="event.status === 'cancelled' || event.status === 'over' || moment(event.ended_at).isBefore(new Date())" class="text-muted event-cancelled">
                                                                    {{ event.type|capitalize }}
                                                                </td>
                                                                <td data-val="type" v-else>
                                                                    {{ event.type|capitalize }}
                                                                </td>

                                                                <td data-val="description" v-if="event.status === 'cancelled' || event.status === 'over' || moment(event.ended_at).isBefore(new Date())" class="text-muted event-cancelled">
                                                                    {{ event.description|sliceString }}
                                                                </td>
                                                                <td data-val="description" v-else>
                                                                    <a href="javascript:void(0)" title="Details" v-if="event.description != null" @click="showEventDetails($event, event.location, event.description)">
                                                                        {{ event.description|sliceString }} <i class="fas fa-angle-down"></i>
                                                                    </a>
                                                                </td>

                                                                <td data-val="description" v-if="event.status === 'cancelled' || event.status === 'over' || moment(event.ended_at).isBefore(new Date())" class="text-right event-cancelled">
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                </td>
                                                                <td data-val="type" v-else class="text-right">
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>


                                                                    <button class="btn btn-outline-secondary btn-sm" title="Duplicate" @click="$event.preventDefault(), duplicateEvent(index)"><i class="far fa-clone"></i></button>
                                                                    <button class="btn btn-outline-secondary btn-sm" title="Cancel" @click="$event.preventDefault(), cancelEvent(index)"><i class="fas fa-ban"></i></button>
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), removeEvent(index)"><i class="far fa-trash-alt"></i></button>


                                                                </td>


                                                            </tr>

                                                            <tr  class="event-details-header">
                                                                <td v-if="showEditedEventDetails" colspan="8">Event details:</td>
                                                            </tr>
                                                            <tr  class="event-details">
                                                                <td v-if="showEditedEventDetails" colspan="4">Location: {{ detailsEventLocation }}</td>
                                                                <td v-if="showEditedEventDetails" colspan="4">Notes: {{ detailsEventDescription }}</td>
                                                            </tr>


                                                            </tbody>
                                                        </table>
                                                    </div>

                                                -->

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">

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
                                                                            <label><small>Location: {{ editedEventData.location }}</small></label>
                                                                            <vue-google-autocomplete
                                                                                :id="'map'+editedEventData.id"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                            ></vue-google-autocomplete>
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
                                                                            <label><small>Description [max 150 symbols]</small></label>
                                                                            <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" @input="assertEventDescriptionMaxChars" name="event-description">
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
                                                                        <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="formRequestProcess" @click="saveEvent"><i class="far fa-save"></i> Save Event Data</button>
                                                                        <button class="btn btn-danger btn-sm pull-right btn-open" title="Cancel" :disabled="formRequestProcess" @click="hideAddEventForm"><i class="far fa-times-circle"></i> Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- END Event Actions -->
                                                        </div>
                                                    </transition>

                                                </div>

                                            </div>

                                            <div class="row modal-bottom">
                                                <div class="col-md-4">
                                                    <button class="btn btn-outline-primary btn-sm" @click="showAddEventForm" :disabled="formRequestProcess">+ Add Event</button>
                                                </div>

                                                <div class="col-md-8">

                                                        <div v-if="requestSuccess" class="alert alert-success fade show" role="alert">
                                                            <strong>Success!</strong> {{ requestSuccess }}
                                                        </div>


                                                        <div v-if="requestDanger" class="alert alert-danger fade show" role="alert">
                                                            <strong>Error!</strong> {{ requestDanger }}
                                                        </div>


                                                </div>
                                            </div>

                                        </form>
                                    </div>

                                    <div class="modal-footer">

                                        <div v-if="showCancelModalAlert" class="alert alert-warning" role="alert">
                                            Please save all data before closing modal...
                                        </div>

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
                                                <div class="input-group input-group-sm mb-3 col-5">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Calendar name</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="calendar_name" v-model="current_calendar.name" required :disabled="formRequestProcess">
                                                </div>
                                                <div class="invalid-feedback">Please provide a valid Calendar Name.</div>
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Owner's Email Address</span>
                                                        </div>
                                                        <input type="email" class="form-control" name="owner_email_address" v-model="current_calendar.user.email" required readonly :disabled="formRequestProcess">
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="form-group form-check form-group-sm">
                                                        <input class="form-check-input" type="checkbox" value="" id="duplicateOwnerByMeCheckbox" checked disabled>
                                                        <label class="form-check-label" for="duplicateOwnerByMeCheckbox">
                                                            Owned by me
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <label>Events: {{ current_calendar.events.length }}</label>
                                                <div class="card col-md-12">

                                                    <div id="duplicatedCalendarEventsContent" class="card-body">

                                                        <div class="row header">
                                                            <div class="col-3">Date</div>
                                                            <div class="col-2">Address</div>
                                                            <div class="col-2">Event type</div>
                                                            <div class="col-2">Notes</div>
                                                            <div class="col-1">Status</div>
                                                            <div class="col-2 text-right">Actions</div>
                                                        </div>
                                                        <hr>
                                                        <div  class="content">
                                                            <div v-for="(event, index) in current_calendar.events" :data-index="index">

                                                                <div class="row" v-bind:class="{ 'over-status': moment(event.ended_at).isBefore(new Date()), 'cancelled-status': event.status === 'cancelled', 'deleted-status': event.status === 'deleted'  }">
                                                                    <div class="col-3 date">
                                                                        {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                    </div>
                                                                    <div class="col-2">
                                                                        <a href="javascript:void(0)" :title="event.location">
                                                                            {{ event.location|sliceString }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-2">
                                                                        {{ event.type|capitalize }}
                                                                    </div>
                                                                    <div class="col-2">
                                                                        <a href="javascript:void(0)" :title="event.description">
                                                                            {{ event.description|sliceString }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-1">
                                                                        <span v-if="event.status === 'over'" class="badge badge-info event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'confirmed'" class="badge badge-success event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'cancelled'" class="badge badge-warning event-status-badge">{{event.status}}</span>
                                                                        <span v-else-if="event.status === 'deleted'" class="badge badge-danger event-status-badge">{{event.status}}</span>
                                                                    </div>

                                                                    <div class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Edit" @click="$event.preventDefault(), editEditedEvent(index)">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), removeDuplicatedEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <!--

                                                                    <div v-if="event.status === 'over'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'cancelled'" class="col-2 text-right actions">
                                                                        <button v-if="event.current_status" class="btn btn-outline-danger btn-sm" title="Restore" @click="$event.preventDefault(), restoreEditedEventStatus(index)">
                                                                            <i class="fas fa-trash-restore-alt"></i>
                                                                        </button>
                                                                        <button v-else class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'deleted'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-danger btn-sm" title="Restore" @click="$event.preventDefault(), restoreEditedEventStatus(index)">
                                                                            <i class="fas fa-trash-restore-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else-if="event.status === 'duplicated'" class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Edit" @click="$event.preventDefault(), editEditedEvent(index)">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), removeDuplicatedEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>

                                                                    <div v-else class="col-2 text-right actions">
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Edit" @click="$event.preventDefault(), editEditedEvent(index)">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>

                                                                        <button class="btn btn-outline-secondary btn-sm" title="Duplicate" @click="$event.preventDefault(), duplicateEditedEvent(index)">
                                                                            <i class="far fa-clone"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-secondary btn-sm" title="Cancel" @click="$event.preventDefault(), markCancelEditedEvent(index)">
                                                                            <i class="fas fa-ban"></i>
                                                                        </button>

                                                                        <button class="btn btn-outline-danger btn-sm" title="Delete" @click="$event.preventDefault(), markRemoveEditedEvent(index)">
                                                                            <i class="far fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                    -->
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!--
                                                    <div class="card-body">
                                                        <table class="table table-sm">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col">Date</th>
                                                                <th scope="col">Address</th>
                                                                <th scope="col">Event type</th>
                                                                <th scope="col">Notes</th>
                                                                <th scope="col" class="actions"></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="(event, index) in current_calendar.events" :data-index="index">
                                                                <td data-val="startDate">
                                                                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
                                                                </td>

                                                                <td data-val="location">
                                                                    <a href="javascript:void(0)" title="Details" v-if="event.location != null" @click="showEventDetails($event, event.location, event.description)">
                                                                        {{ event.location|sliceString }} <i class="fas fa-angle-down"></i>
                                                                    </a>
                                                                </td>

                                                                <td data-val="type">
                                                                    {{ event.type|capitalize }}
                                                                </td>
                                                                <td data-val="description">
                                                                    <a href="javascript:void(0)" title="Details" v-if="event.description != null" @click="showEventDetails($event, event.location, event.description)">
                                                                        {{ event.description|sliceString }} <i class="fas fa-angle-down"></i>
                                                                    </a>
                                                                </td>
                                                                <td class="text-right">
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                    <button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="More" onclick="event.preventDefault(); return false;"><i class="fas fa-ellipsis-h"></i></button>
                                                                </td>
                                                            <tr class="event-details-header">
                                                                <td v-if="showEditedEventDetails" colspan="8">Event details:</td>
                                                            </tr>
                                                            <tr class="event-details">
                                                                <td v-if="showEditedEventDetails" colspan="4">Location: {{ detailsEventLocation }}</td>
                                                                <td v-if="showEditedEventDetails" colspan="4">Notes: {{ detailsEventDescription }}</td>
                                                            </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    -->

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">

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
                                                                            <label><small>Location: {{ editedEventData.location }}</small></label>
                                                                            <vue-google-autocomplete
                                                                                id="map"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                            ></vue-google-autocomplete>
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
                                                                            <label><small>Description [max 150 symbols]</small></label>
                                                                            <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" @input="assertEventDescriptionMaxChars" name="event-description">
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
                                                                        <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="formRequestProcess" @click="saveEvent"><i class="far fa-save"></i> Save Event Data</button>
                                                                        <button class="btn btn-danger btn-sm pull-right btn-open" title="Cancel" :disabled="formRequestProcess" @click="hideAddEventForm"><i class="far fa-times-circle"></i> Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- END Event Actions -->
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
                                        <div v-if="showCancelModalAlert" class="alert alert-warning" role="alert">
                                            Please save all data before closing modal...
                                        </div>
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
                                                <div class="input-group input-group-sm mb-3 col-5">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Calendar name</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="calendar_name" v-model="new_calendar.name" required :disabled="formRequestProcess">
                                                </div>
                                                <div class="invalid-feedback">Please provide a valid Calendar Name.</div>
                                                <div class="col-5">
                                                    <div class="input-group input-group-sm mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Owner's Email Address</span>
                                                        </div>
                                                        <input type="email" class="form-control" name="owner_email_address" v-model="owner_email_address" required readonly :disabled="formRequestProcess">
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="form-group form-check form-group-sm">
                                                        <input class="form-check-input" type="checkbox" value="" id="newOwnerByMeCheckbox" checked disabled>
                                                        <label class="form-check-label" for="newOwnerByMeCheckbox">
                                                            Owned by me
                                                        </label>
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
                                                                <th scope="col">Address</th>
                                                                <th scope="col">Event type</th>
                                                                <th scope="col">Notes</th>
                                                                <th scope="col" class="actions"></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="(event, index) in current_calendar.events" :data-index="index">
                                                                <td data-val="startDate">
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
                                                                    <!--<button class="btn btn-outline-secondary btn-sm" title="Edit" @click="editEvent(index, $event)"><i class="fas fa-pencil-alt"></i></button>-->
                                                                    <button class="btn btn-outline-danger btn-sm" title="Delete" @click="removeEvent(index, $event)"><i class="far fa-trash-alt"></i></button>
                                                                    <!--<button class="btn btn-outline-secondary btn-sm" :disabled="event.id === 'new'" title="More" onclick="event.preventDefault(); return false;"><i class="fas fa-ellipsis-h"></i></button>-->
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">

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
                                                                            <vue-google-autocomplete
                                                                                ref="eventNewLocationAutocomplete"
                                                                                id="map"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                            ></vue-google-autocomplete>
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
                                                                            <label><small>Description [max 150 symbols]</small></label>
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
                                                                        <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="formRequestProcess" @click="saveEvent"><i class="far fa-save"></i> Save Event Data</button>
                                                                        <button class="btn btn-danger btn-sm pull-right btn-open" title="Cancel" :disabled="formRequestProcess" @click="hideAddEventForm"><i class="far fa-times-circle"></i> Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- END Event Actions -->
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
                                        <div v-if="showCancelModalAlert" class="alert alert-warning" role="alert">
                                            Please save all data before closing modal...
                                        </div>
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
	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import moment from 'moment';
    import vueGoogleAutocomplete from 'vue-google-autocomplete'

	export default {

        components: {
            datePicker,
            vueGoogleAutocomplete
        },

		data() {
            return {
                dateOptions: {
                    format: 'M/DD/YYYY',
                    useCurrent: true,
                    ignoreReadonly: true
                },

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
                current_calendar: {
                    events: []
                },
                appendToBody: false,

                showCancelModalAlert: false,

                showEditedEventDetails: false,
                showEventDropdownActions: false,
                detailsEventLocation: '',
                detailsEventDescription: '',


                new_calendar: {
                    user: {
                        email: null
                    },
                    summary: '',
                    events: []
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

                moment: moment
			}
		},

		created() {
			this.$root.$refs.addEditCalendarModal = this;
		},



        mounted() {
            this.csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            this.owner_email_address = document.querySelector('meta[name="current_user_email"]').getAttribute('content');




        },

		computed:  {
			// newEventDataValid() {
            //     let result = true;
            //     if (!this.editedEventData.location || this.editedEventData.location === '') {
            //         result = false;
            //     }
            //     if (!this.editedEventData.description || this.editedEventData.description === '') {
            //         result = false;
            //     }
            //     return result;
			// },


		},

		methods: {



            getAddressData: function (addressData, placeResultData, id) {
                this.editedEventData.location = addressData.newVal;
            },

            assertEventDescriptionMaxChars: function() {
                if (this.editedEventData.description.length > 150) {
                    this.editedEventData.description = this.editedEventData.description.substring(0, 150);
                }
            },

            showEventDetails: function(event, location, description) {
                event.stopPropagation();
                this.showNewEventDataForm = false;
                this.showEditedEventDetails = !this.showEditedEventDetails;
                this.detailsEventLocation = location;
                this.detailsEventDescription = description;
            },

		    // Edit calendar Methods
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
                if (!this.showCancelModalAlert) {
                    this.showCancelModalAlert = true;
                } else {
                    this.requestDanger = false;
                    this.requestSuccess = false;
                    this.calendar_name = '';
                    this.calendar_events = [];
                    this.showEditCalendarModal = false;
                    this.editedEventData = {
                        index: null,
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
                    };
                    this.showNewEventDataForm = false;
                    this.showCancelModalAlert = false;
                }
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
                this.editedEventData.index = null;
                this.editedEventData = {
                    index: null,
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
                    type: 'game',
                    description: null
                };
                this.showNewEventDataForm = true;


                this.showEditedEventDetails = false;
            },
            hideAddEventForm: function(event) {
                event.preventDefault();
                this.editedEventData = {
                    index: null,
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
                };
                this.showNewEventDataForm = false;
            },
            saveEvent: function(event) {
                event.preventDefault();

                if (this.editedEventData.index === null) {
                    // Add new Event
                    let started_at = this.getDateTime(this.editedEventData.startDate, this.editedEventData.startTimeHours, this.editedEventData.startTimeMinutes, this.editedEventData.startTimeAmPm);
                    let ended_at = this.getDateTime(this.editedEventData.endDate, this.editedEventData.endTimeHours, this.editedEventData.endTimeMinutes, this.editedEventData.endTimeAmPm);
                    let newEvent = {
                        id: 'new',
                        started_at: started_at,
                        ended_at: ended_at,
                        location: this.editedEventData.location,
                        type: this.editedEventData.type,
                        description: this.editedEventData.description
                    };
                    this.current_calendar.events.push(newEvent);

                    this.requestSuccess = 'Save event data success';

                    this.editedEventData = {
                        index: null,
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
                    };

                        this.showNewEventDataForm = false;
                        this.requestSuccess = false;

                } else {
                    // Update current event
                    let started_at = this.getDateTime(this.editedEventData.startDate, this.editedEventData.startTimeHours, this.editedEventData.startTimeMinutes, this.editedEventData.startTimeAmPm);
                    this.current_calendar.events[this.editedEventData.index].started_at = started_at;

                    let ended_at = this.getDateTime(this.editedEventData.endDate, this.editedEventData.endTimeHours, this.editedEventData.endTimeMinutes, this.editedEventData.endTimeAmPm);
                    this.current_calendar.events[this.editedEventData.index].ended_at = ended_at;
                    this.current_calendar.events[this.editedEventData.index].location = this.editedEventData.location;
                    this.current_calendar.events[this.editedEventData.index].type = this.editedEventData.type;
                    this.current_calendar.events[this.editedEventData.index].description = this.editedEventData.description;

                    this.requestSuccess = 'Save event data success';

                    this.editedEventData = {
                        index: null,
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
                    };
                    this.showNewEventDataForm = false;
                    this.requestSuccess = false;
                }
            },

            markRemoveEditedEvent: function(index) {
                this.current_calendar.events[index].current_status = this.current_calendar.events[index].status;
                this.current_calendar.events[index].status = 'deleted';
                this.current_calendar.events[index].action = 'delete';

                    //this.current_calendar.events.splice(index, 1);

                // if (this.new_calendar) {
                //     this.new_calendar.events.splice(index, 1);
                // }
            },
            restoreEditedEventStatus: function(index) {
               if(this.current_calendar.events[index].current_status) {
                   this.current_calendar.events[index].status = this.current_calendar.events[index].current_status;
               }
            },
            editEditedEvent: function(index) {

                let currentEvent = this.current_calendar.events[index];
                this.editedEventData = {
                    index: index,
                    id: currentEvent.id,
                    startDate: this.$options.filters.formatDate(currentEvent.started_at),
                    startTimeHours: this.$options.filters.formatHours(currentEvent.started_at),
                    startTimeMinutes: this.$options.filters.formatMinutes(currentEvent.started_at),
                    startTimeAmPm: this.$options.filters.formatAmPm(currentEvent.started_at),
                    endDate: this.$options.filters.formatDate(currentEvent.ended_at),
                    endTimeHours: this.$options.filters.formatHours(currentEvent.ended_at),
                    endTimeMinutes: this.$options.filters.formatMinutes(currentEvent.ended_at),
                    endTimeAmPm: this.$options.filters.formatAmPm(currentEvent.ended_at),
                    location: currentEvent.location,
                    type: currentEvent.type,
                    description: currentEvent.description
                };
                this.showNewEventDataForm = true;
            },
            markCancelEditedEvent: function(index) {
                this.current_calendar.events[index].current_status = this.current_calendar.events[index].status;
                this.current_calendar.events[index].status = 'cancelled';
                this.current_calendar.events[index].action = 'cancelled';
            },

            duplicateEditedEvent: function(index) {
                let duplicatedEvent = this.current_calendar.events[index];
                duplicatedEvent.status = 'duplicated';
                duplicatedEvent.id = 'new';
                this.current_calendar.events.push(duplicatedEvent);
                console.log(this.current_calendar.events[index]);
            },
            removeDuplicatedEditedEvent: function(index) {
                let eventsArray = this.current_calendar.events;
                eventsArray.splice(index, 1);
                this.current_calendar.events = eventsArray;
            },
            // END Edit calendar Methods

            // Add calendar Methods
            showAddCalendarModalAction: function() {
                this.current_calendar.events = [];
                this.calendar_name = '';
                this.calendar_events = [];
                this.showAddCalendarModal = true;
            },
            hideAddCalendarModalAction: function() {
                if (!this.showCancelModalAlert) {
                    this.showCancelModalAlert = true;
                } else {
                    this.requestDanger = false;
                    this.requestSuccess = false;
                    this.calendar_name = '';
                    this.calendar_events = [];
                    this.showAddCalendarModal = false;
                    this.editedEventData = {
                        index: null,
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
                    };
                    this.showNewEventDataForm = false;
                    this.showCancelModalAlert = false;
                }
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
                                currentObj.current_calendar.events = [];

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
            // END Add calendar Methods

            // Duplicate Calendar Methods
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
                if (!this.showCancelModalAlert) {
                    this.showCancelModalAlert = true;
                } else {
                    this.requestDanger = false;
                    this.requestSuccess = false;
                    this.calendar_name = '';
                    this.calendar_events = [];
                    this.showDuplicateCalendarModal = false;
                    this.editedEventData = {
                        index: null,
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
                    };
                    this.showNewEventDataForm = false;
                    this.showCancelModalAlert = false;
                }
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
            // END Duplicate Calendar Methods


            // Common methods
            editEvent: function(index, event) {
                event.preventDefault();

                let currentEvent = this.current_calendar ? this.current_calendar.events[index] : this.new_calendar.events[index];

                this.editedEventData = {
                    index: index,
                    id: currentEvent.id,
                    startDate: this.$options.filters.formatDate(currentEvent.started_at),
                    startTimeHours: this.$options.filters.formatHours(currentEvent.started_at),
                    startTimeMinutes: this.$options.filters.formatMinutes(currentEvent.started_at),
                    startTimeAmPm: this.$options.filters.formatAmPm(currentEvent.started_at),
                    endDate: this.$options.filters.formatDate(currentEvent.ended_at),
                    endTimeHours: this.$options.filters.formatHours(currentEvent.ended_at),
                    endTimeMinutes: this.$options.filters.formatMinutes(currentEvent.ended_at),
                    endTimeAmPm: this.$options.filters.formatAmPm(currentEvent.ended_at),
                    location: currentEvent.location,
                    type: currentEvent.type,
                    description: currentEvent.description
                };
                this.showNewEventDataForm = true;
                this.showEditedEventDetails = false;
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
            },
		},

		filters: {
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
            },
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
                let ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                hours = hours < 10 ? '0'+hours : hours;
                minutes = minutes < 10 ? '0'+minutes : minutes;
                return hours+':'+minutes+' '+ampm;
            },
            sliceString: function(value) {
                if (value && value.length > 10) {
                    let sliced = value.slice(0,10);
                    if (sliced.length < value.length) {
                        sliced += '...';
                    }
                    return sliced;
                } else {
                    return value;
                }
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


