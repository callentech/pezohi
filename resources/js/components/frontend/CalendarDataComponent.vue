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
                    <div class="col-6">
                        <div class="title">
                            <img :src="'/img/soccer_ball.png'" alt="">
                            <span class="text">{{ calendar.name }}</span>
                        </div>
                        <div class="description">
                            {{ calendar.events.length }} events <i class="fas fa-circle"></i>
                            Owned by : {{ calendar.user.name }} <i class="fas fa-circle"></i>
                            Last updated : {{ calendar.updated|formatDate() }}
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="actions mt-2">
                            <button type="button" class="btn btn-primary"><i class="fas fa-bell"></i> Subscribe</button>
                            <button type="button" class="btn btn-primary"><i class="fas fa-user-plus"></i> Share</button>
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="event in  sortedEvents" >
                                <td>{{ event.started_at|formatEventDate }}</td>
                                <td> {{ event.started_at|formatEventTime }} - {{ event.ended_at|formatEventTime }}</td>
                                <td><a href="javascript:void(0)" :title="event.location">{{ event.location|sliceString }}</a></td>
                                <td>{{ event.type|capitalize }}</td>
                                <td><a href="javascript:void(0)" :title="event.description">{{ event.description|sliceString }}</a></td>
                                <td>{{ event.status|capitalize }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

export default {
    props:['data'],
    data() {
        return {
            calendar: this.data,

            sortByDateDirection: 'desc',
            sortByLocationDirection: 'desc',
            sortByTypeDirection: 'desc',
            sortByDescriptionDirection: 'desc',
            sortByStatusDirection: 'desc',

            sortedEvents: []
        }
    },

    methods: {

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
            let sliced = value.slice(0,20);
            if (sliced.length < value.length) {
                sliced += '...';
            }
            return sliced;
        },

        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        }
    }
}

</script>
