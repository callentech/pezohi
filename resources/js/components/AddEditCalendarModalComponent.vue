
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

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">

                                                            <!-- Event data -->
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Date</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <date-picker v-model="editedEventData.startDate" :config="dateOptions" readonly name="event-start-date"></date-picker>
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Start time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.startTime" class="custom-select" name="startTime" @change="selectTimeAction('start')">
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                 <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>End time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.endTime" class="custom-select" name="endTime" @change="selectTimeAction('end')">
                                                                                
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Type</small></label>
                                                                            <select v-model="editedEventData.type" class="form-control form-control-sm" name="type">
                                                                                <option value="game">Game</option>
                                                                                <option value="practice">Practice</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-4">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Address</small></label>
                                                                            <vue-google-autocomplete
                                                                                ref="eventLocationAutocomplete"
                                                                                :id="'map'+editedEventData.id"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                                name="location"
                                                                            ></vue-google-autocomplete>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Notes [max 150 symbols]</small></label>
                                                                            <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" @input="assertEventDescriptionMaxChars" name="notes">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- END Event data -->

                                                            <hr>
                                                            <!-- Event Actions -->
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <div class="data">
                                                                        <button class="btn btn-success btn-sm pull-right btn-open" title="Save" :disabled="!newEventDataValid || formRequestProcess" @click="saveEvent"><i class="far fa-save"></i> Save Event Data</button>
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

                                                    <transition name="fade">
                                                        <div class="card-footer" v-if="showNewEventDataForm">

                                                           <!-- Event data -->
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Date</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <date-picker v-model="editedEventData.startDate" :config="dateOptions" readonly name="event-start-date"></date-picker>
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Start time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.startTime" class="custom-select" name="startTime" @change="selectTimeAction('start')">
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                 <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>End time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.endTime" class="custom-select" name="endTime" @change="selectTimeAction('end')">
                                                                                
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Type</small></label>
                                                                            <select v-model="editedEventData.type" class="form-control form-control-sm" name="type">
                                                                                <option value="game">Game</option>
                                                                                <option value="practice">Practice</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-4">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Address</small></label>
                                                                            <vue-google-autocomplete
                                                                                ref="eventLocationAutocomplete"
                                                                                :id="'map'+editedEventData.id"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                                name="location"
                                                                            ></vue-google-autocomplete>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Notes [max 150 symbols]</small></label>
                                                                            <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" @input="assertEventDescriptionMaxChars" name="notes">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- END Event data -->

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
                                                        </div>

                                                        <div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
                                                            <strong>Error!</strong> {{ requestDanger }}
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
                                                <label>Events: {{ current_calendar.events.length }}</label>
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
                                                                    {{ event.started_at|formatDate }} {{ event.started_at|formatTime }} - 
                                                                    {{ event.ended_at|formatDate }} {{ event.ended_at|formatTime }}
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

                                                            <!-- Event Data -->
                                                            <hr>
                                                            <div class="row">
                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Date</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <date-picker v-model="editedEventData.startDate" :config="dateOptions" readonly name="event-start-date"></date-picker>
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>Start time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.startTime" class="custom-select" name="startTime" @change="selectTimeAction('start')">
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                 <div class="col-2">
                                                                    <div class="data">
                                                                        <label><small>End time</small></label>
                                                                        <div class="input-group input-group-sm mb-3">
                                                                            <select v-model="editedEventData.endTime" class="custom-select" name="endTime" @change="selectTimeAction('end')">
                                                                                
                                                                                <option value="00:30 AM">00:30 AM</option>
                                                                                <option value="01:00 AM">01:00 AM</option>
                                                                                <option value="01:30 AM">01:30 AM</option>
                                                                                <option value="02:00 AM">02:00 AM</option>
                                                                                <option value="02:30 AM">02:30 AM</option>
                                                                                <option value="03:00 AM">03:00 AM</option>
                                                                                <option value="03:30 AM">03:30 AM</option>
                                                                                <option value="04:00 AM">04:00 AM</option>
                                                                                <option value="04:30 AM">04:30 AM</option>
                                                                                <option value="05:00 AM">05:00 AM</option>
                                                                                <option value="05:30 AM">05:30 AM</option>
                                                                                <option value="06:00 AM">06:00 AM</option>
                                                                                <option value="06:30 AM">06:30 AM</option>
                                                                                <option value="07:00 AM">07:00 AM</option>
                                                                                <option value="07:30 AM">07:30 AM</option>
                                                                                <option value="08:00 AM">08:00 AM</option>
                                                                                <option value="08:30 AM">08:30 AM</option>
                                                                                <option value="09:00 AM">09:00 AM</option>
                                                                                <option value="09:30 AM">09:30 AM</option>
                                                                                <option value="10:00 AM">10:00 AM</option>
                                                                                <option value="10:30 AM">10:30 AM</option>
                                                                                <option value="11:00 AM">11:00 AM</option>
                                                                                <option value="11:30 AM">11:30 AM</option>
                                                                                <option value="12:00 AM">12:00 AM</option>
                                                                                <option value="12:30 AM">12:30 AM</option>
                                                                                <option value="01:00 PM">01:00 PM</option>
                                                                                <option value="01:30 PM">01:30 PM</option>
                                                                                <option value="02:00 PM">02:00 PM</option>
                                                                                <option value="02:30 PM">02:30 PM</option>
                                                                                <option value="03:00 PM">03:00 PM</option>
                                                                                <option value="03:30 PM">03:30 PM</option>
                                                                                <option value="04:00 PM">04:00 PM</option>
                                                                                <option value="04:30 PM">04:30 PM</option>
                                                                                <option value="05:00 PM">05:00 PM</option>
                                                                                <option value="05:30 PM">05:30 PM</option>
                                                                                <option value="06:00 PM">06:00 PM</option>
                                                                                <option value="06:30 PM">06:30 PM</option>
                                                                                <option value="07:00 PM">07:00 PM</option>
                                                                                <option value="07:30 PM">07:30 PM</option>
                                                                                <option value="08:00 PM">08:00 PM</option>
                                                                                <option value="08:30 PM">08:30 PM</option>
                                                                                <option value="09:00 PM">09:00 PM</option>
                                                                                <option value="09:30 PM">09:30 PM</option>
                                                                                <option value="10:00 PM">10:00 PM</option>
                                                                                <option value="10:30 PM">10:30 PM</option>
                                                                                <option value="11:00 PM">11:00 PM</option>
                                                                                <option value="11:30 PM">11:30 PM</option>
                                                                                <option value="12:00 PM">12:00 PM</option>
                                                                            </select>
                                                                            <div class="input-group-append">
                                                                                <label class="input-group-text"><i class="far fa-clock"></i></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-2">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Type</small></label>
                                                                            <select v-model="editedEventData.type" class="form-control form-control-sm" name="type">
                                                                                <option value="game">Game</option>
                                                                                <option value="practice">Practice</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-4">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Address</small></label>
                                                                            <vue-google-autocomplete
                                                                                ref="eventLocationAutocomplete"
                                                                                :id="'map'+editedEventData.id"
                                                                                classname="form-control form-control-sm"
                                                                                placeholder="Change Event Location"
                                                                                v-on:inputChange="getAddressData"
                                                                                name="location"
                                                                            ></vue-google-autocomplete>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="data">
                                                                        <div class="form-group">
                                                                            <label><small>Notes [max 150 symbols]</small></label>
                                                                            <input type="text" v-model="editedEventData.description" class="form-control form-control-sm" @input="assertEventDescriptionMaxChars" name="notes">
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
														</div>

														<div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
															<strong>Error!</strong> {{ requestDanger }}
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
                    startTime: null,
                    endTime: null,
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
			newEventDataValid() {
                let result = true;
                if (this.editedEventData.startTime === '' || this.editedEventData.endTime === '') {
                    result = false;
                }
                return result;
            }


		},

		methods: {

            selectTimeAction: function(select) {
                this.requestDanger = null;
                let fromdt = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.startTime;
                let from = new Date(Date.parse(fromdt));
                if (select === 'start') {
                    let endTime = moment(from.setHours(from.getHours() + 1)).format('hh:mm a').toUpperCase();
                    this.editedEventData.endTime = endTime;
                } else if (select === 'end') {
                    let todt = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.endTime;
                    let to = new Date(Date.parse(todt));

                    if (from > to) {
                        let endTime = moment(from.setHours(from.getHours() + 1)).format('hh:mm a').toUpperCase();
                        this.editedEventData.endTime = endTime;
                        this.requestDanger = 'Please set correct datetime range';
                    }
                }
            },

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
                        startTime: null,
                        endTime: null,
                    
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
                let startDateTime = moment();
                startDateTime.add(30, 'minutes').startOf('hour');
                let startTime = (startDateTime.format('hh:mm a')).toUpperCase();
                startDateTime.add(1, 'hours');
                let endTime = (startDateTime.format('hh:mm a')).toUpperCase();
               
                this.editedEventData = {
                    index: null,
                    id: null,
                    startDate: moment(),
                    startTime: startTime,
                    endTime: endTime,
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
                    startTime: null,
                    endTime: null,
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
                event.stopPropagation();
                
                if (this.editedEventData.index === null) {


                    // Add new Event
                    let started_at = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.startTime;
                    let ended_at = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.endTime;
                    // let started_at = this.editedEventData.startDate+' '+this.editedEventData.startTime;
                    // let ended_at = this.editedEventData.startDate+' '+this.editedEventData.endTime;

                    let newEvent = {
                        id: 'new',
                        started_at: started_at,
                        ended_at: ended_at,
                        location: this.editedEventData.location,
                        type: this.editedEventData.type,
                        description: this.editedEventData.description
                    };
                    this.current_calendar.events.push(newEvent);

                    this.showNewEventDataForm = false;
                    this.requestSuccess = false;


                } else {
                    // Update current event
                    let started_at = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.startTime;
                    let ended_at = moment(this.editedEventData.startDate).format('M/DD/YYYY')+' '+this.editedEventData.endTime;
                    this.current_calendar.events[this.editedEventData.index].startTime = this.editedEventData.startTime;
                    this.current_calendar.events[this.editedEventData.index].endTime = this.editedEventData.endTime;
                    this.current_calendar.events[this.editedEventData.index].started_at = started_at;
                    this.current_calendar.events[this.editedEventData.index].ended_at = ended_at;

                    this.current_calendar.events[this.editedEventData.index].location = this.editedEventData.location;
                    this.current_calendar.events[this.editedEventData.index].type = this.editedEventData.type;
                    this.current_calendar.events[this.editedEventData.index].description = this.editedEventData.description;

                     this.requestSuccess = 'Save event data success';

                    this.editedEventData = {
                        index: null,
                        id: null,
                        startDate: null,
                        startTime: null,
                        endTime: null,
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

            removeEvent: function(index, event) {
                event.preventDefault();
                 let eventsArray = this.current_calendar.events;
                eventsArray.splice(index, 1);
                this.current_calendar.events = eventsArray;
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
                    startDate: moment(currentEvent.started_at),
                    
                    //startTime: this.$options.filters.formatTime(currentEvent.started_at),
                    //endTime: this.$options.filters.formatTime(currentEvent.ended_at),
                    
                    startTime: moment(currentEvent.started_at).format('hh:mm a').toUpperCase(),
                    endTime: moment(currentEvent.ended_at).format('hh:mm a').toUpperCase(),
                    
                    
                    
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
                    currentObj.requestSuccess = false;
                    currentObj.requestDanger = false;
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

                           
                            currentObj.requestDanger = response.data.data.message ? response.data.data.message : 'Request Error';
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

                let dateTime = new Date(Date.parse(value));
                return moment(dateTime).format('hh:mm a').toUpperCase();

                
                // let date = new Date(value);
                // let hours = date.getHours();
                // let ampm = hours >= 12 ? 'PM' : 'AM';
                // let minutes = date.getMinutes();
                // hours = hours % 12;
                // ampm = hours == 0 ? 'AM' : ampm;
                // hours = hours  < 10 ? '0'+hours : hours;
                // minutes = Math.ceil(minutes/30)*30;
                // minutes = 60 ? 0 : minutes;
                // minutes = minutes < 10 ? '0'+minutes : minutes;
                // return hours +':'+minutes + ' '+ampm;

            //     

            // 
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


