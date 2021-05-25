<template>

    <div class="content-wrapper">
        <div class="header">
            <div class="container">
                <div v-if="calendar === 404" class="row p-3 px-md-4 mb-3">
                    <div class="col-6">
                        <div class="title">
                            <span class="text">404 Calendar not found ...</span>
                        </div>
                    </div>
                </div>
                <div v-else class="row p-3 px-md-4 mb-3">
                    <div class="col-4">
                        <div class="title">
                            <img :src="'/img/soccer_ball.png'" alt="">
                            <span class="text">{{ calendar.name }}</span>
                        </div>
                        <div class="description">
                            {{ calendar.events.length }} events <i class="fas fa-circle"></i>
                            Owned by : {{ calendar.user.email }} <i class="fas fa-circle"></i>
                            Last updated : {{ calendar.updated_at|formatDate }}
                        </div>
                    </div>
                    <div class="col-4">
                        <div v-if="requestSuccess" class="alert alert-success" role="alert">
                            {{ requestSuccess }}
                        </div>
                        <div v-if="requestError" class="alert alert-danger" role="alert">
                            {{ requestError }}
                        </div>
                    </div>
                    <div v-if="user.status && user.status === 'anonimous'" class="col-md-4 text-right">
                        <div class="actions mt-2">
                            <button v-if="!calendar.isSubscribed" type="button" class="btn btn-primary" @click="showSubscribeModal = true"><i class="fas fa-bell"></i> Subscribe</button>
                            <button v-else type="button" class="btn btn-primary" @click="unsubscribeCalendar(calendar.id)"><i class="fas fa-bell"></i> Unsubscribe</button>
                            <button type="button" class="btn btn-primary" @click="shareCalendar(calendar.publicUrl)"><i class="fas fa-user-plus"></i> Share</button>
                        </div>
                    </div>
                    <div v-else class="col-md-4 text-right">
                        <div v-if="user.jobs_status === 'finished'" class="actions mt-2">
<!--                            <button v-if="!calendar.isSubscribed" type="button" class="btn btn-primary" @click="subscribeCalendar(calendar.id)"><i class="fas fa-bell"></i> Subscribe</button>-->
                            <button v-if="!calendar.isSubscribed" type="button" class="btn btn-primary" @click="showSubscribeModal = true"><i class="fas fa-bell"></i> Subscribe</button>
                            <button v-else type="button" class="btn btn-primary" @click="unsubscribeCalendar(calendar.id)"><i class="fas fa-bell"></i> Unsubscribe</button>
                            <button type="button" class="btn btn-primary" @click="shareCalendar(calendar.publicUrl)"><i class="fas fa-user-plus"></i> Share</button>
                        </div>
                        <div v-else class="actions mt-2">
                            <div class="alert alert-info" role="alert">
                                Sync in process. Please wait ...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="calendar !== 404" class="content">
            <div class="container">
                <div class="row p-3 px-md-4 mb-3">
                    <table class="table table-bordered calendar-events-table">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByDate">
                                        Date
                                        <i v-if="sortByDateDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                        <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                    </a>
                                </th>
                                <th scope="col">Time</th>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByLocation">
                                        Address
                                        <i v-if="sortByLocationDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                        <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByType">
                                        Event type
                                        <i v-if="sortByTypeDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                        <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByDescription">
                                        Notes
                                        <i v-if="sortByDescriptionDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                        <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link" @click="sortEventsListByStatus">
                                        Status
                                        <i v-if="sortByStatusDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                        <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="javascript:void(0)" class="sort-link">
                                        Actions
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="event in sortedEvents">
                                <td>{{ event.started_at|formatEventDate }}</td>
                                <td>{{ event.started_at|formatEventTime }} - {{ event.ended_at|formatEventTime }}</td>
                                <td><a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a></td>
                                <td>{{ event.type|capitalize }}</td>
                                <td><a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a></td>

                                <td v-if="moment(event.ended_at).isBefore(new Date())">
                                    Over
                                </td>
                                <td v-else>
                                    {{ event.status|capitalize }}
                                </td>

                                <td v-if="!moment(event.ended_at).isBefore(new Date())" width="10%">
                                    <button v-if="!event.attendee" type="button" class="btn btn-primary btn-sm" @click="attendeeEvent(event.id, true);">I'll attend</button>
                                    <button v-else type="button" class="btn btn-outline-success btn-sm" @click="attendeeEvent(event.id, false);">I'll not attend</button>
                                </td>

                                <td v-else></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Share Calendar Message modal -->
        <div v-if="showInfoModal">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-wrapper" @click="hideShareCalendarModal($event)">
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

        <!-- Subscribe modal agronom 25.05.2021-->
        <div v-if="showSubscribeModal">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-wrapper">
                        <div tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Subscribe</h5>
                                        <button type="button" class="close" @click="showSubscribeModal = false">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="" id="userNotify" v-model="userNotify">
                                            <label class="form-check-label" for="userNotify">
                                                I'd like to receive a text message when event is changed
                                            </label>
                                        </div>
                                        <div class="">
                                            <label class="form-label">Phone Number</label>
                                            <input
                                                v-model="userPhone"
                                                @input="clearValidError"
                                                type="tel"
                                                class="form-control"
                                                :class="{'border-danger': !userPhoneValid}"
                                                pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                                            >
                                            <small v-if="!userPhoneValid" class="form-text text-center text-danger">Add phone number please</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="showSubscribeModal = false">Close</button>
                                        <button type="button" class="btn btn-primary" @click="subscribeCalendar(calendar.id)">
                                            <span v-if="subscribeRequestProcess" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subscribe
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        <!-- END Subscribe modal -->
    </div>

</template>

<script>

import moment from 'moment';

export default {
    props:['data', 'user'],
    data() {
        return {
            calendar: this.data,

            sortByDateDirection: 'desc',
            sortByLocationDirection: 'desc',
            sortByTypeDirection: 'desc',
            sortByDescriptionDirection: 'desc',
            sortByStatusDirection: 'desc',

            sortedEvents: [],

            showInfoModal: false,
            infoModalHtml: '',

            requestProcess: false,
            requestError: '',
            requestSuccess: '',

            moment: moment,

            showSubscribeModal: false,
            subscribeRequestProcess: false,
            userNotify: false,
            userPhone: '',
            userPhoneValid: true
        }
    },

    methods: {

        attendeeEvent: function(eventId, attendee) {
            let currentObj = this;
            // Send request
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.requestError = null;
                currentObj.requestSuccess = null;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });
            axios.post('/attendee-event', {eventId: eventId, attendee: attendee})
            .then(function (response) {
                if (response.data.code === 404) {
                    currentObj.requestError = response.data.data.message;
                } else if (response.data.code === 1) {
                    currentObj.requestSuccess = response.data.data.message;
                    currentObj.sortedEvents.map(function(value, key) {
                        if (value.id === eventId) {
                            value.attendee = !value.attendee;
                        }
                    });

                    setTimeout(function() {
                        currentObj.requestSuccess = null;
                    }, 2000);
                } else {
                    currentObj.requestError = 'Request Error';
                }
            })
            .catch(function (error) {
                if (error.response.status === 401) {
                    document.location.href = '/';
                } else {
                    currentObj.requestError = 'Request Error';
                }
            })
            .then(function () {
                currentObj.requestProcess = false;
            });
        },

        unsubscribeCalendar: function(id) {
            let currentObj = this;
            // Send request
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.requestError = null;
                currentObj.requestSuccess = null;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });

            axios.post('/unsubscribe-calendar', {calendar_id: id})
                .then(function (response) {
                    if (response.data.code === 401) {
                        document.location.href = "/";
                    } else if (response.data.code === 404) {
                        currentObj.requestError = response.data.data.message;
                    } else if (response.data.code === 1) {
                        currentObj.requestSuccess = response.data.data.message;
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        currentObj.requestError = 'Request Error';
                    }
                })
                .catch(function (error) {
                    currentObj.requestError = 'Request Error';
                })
                .then(function () {
                    currentObj.requestProcess = false;
                });
        },

        clearValidError: function () {
            this.userPhoneValid = true;
        },

        subscribeCalendar: function(id) {

            if(this.userNotify && !this.userPhone) {
                this.userPhoneValid = false;
                return false;
            }

            let currentObj = this;
            let notify = this.userNotify ? 1 : 0;
            let phone = this.userPhone;
            // Send request
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                currentObj.subscribeRequestProcess = true;
                currentObj.requestError = null;
                currentObj.requestSuccess = null;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });

            axios.post('/subscribe-calendar', {calendar_id: id, notify, phone })
            .then(function (response) {
                if (response.data.code === 401) {
                    document.location.href = "/";
                } else if (response.data.code === 404) {
                    currentObj.requestError = response.data.data.message;
                } else if (response.data.code === 1) {
                    currentObj.requestSuccess = response.data.data.message;
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    currentObj.requestError = 'Request Error';
                }
            })
            .catch(function (error) {
                console.log(error);
                currentObj.requestError = 'Request Error';
            })
            .then(function () {
                currentObj.requestProcess = false;
                currentObj.subscribeRequestProcess = false;
                currentObj.showSubscribeModal = false;
            });
        },

        shareCalendar: function(url) {
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

        hideShareCalendarModal: function(event) {
            if(event.target.classList.contains("modal-wrapper") || event.target.classList.contains("message-modal")) {
                event.stopPropagation();
                event.preventDefault();
                this.infoModalText='';
                this.showInfoModal=false
            }
        },

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
        sortEventsListByStatus: function() {
            this.sortByStatusDirection = this.sortByStatusDirection === 'desc' ? 'asc' : 'desc';
            this.sortedEvents = this.sortArray(this.calendar.events, 'status', this.sortByStatusDirection);
        },
        sortArray: function(array, field, direction) {
            return _.orderBy(array, field, direction);
        }
    },

    mounted() {
        this.sortEventsListByDate();

        if (this.user.jobs_status && this.user.jobs_status === 'started') {
            setInterval(function() {
                location.reload();
            }, 5000);
        }
    },

    filters: {
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
        formatEventDate: function(value) {
            let date = new Date(value);
            let month = date.getMonth() + 1;
            let day = date.getDate();
            let year = date.getFullYear()
            return month+'/'+day+'/'+year;
        },

        formatEventTime: function(value) {
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
            if (value) {
                let sliced = value.slice(0,50);
                if (sliced.length < value.length) {
                    sliced += '...';
                }
                return sliced;
            } else {
                return '';
            }

        },

        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        }
    }
}

</script>
