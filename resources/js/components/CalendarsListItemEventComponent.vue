<template>
	<div class="card calendar-single">

		<div class="row">
			<div class="col-2">
				{{ event.startDate }}
			</div>

			<div class="col-2">
				{{ event.startTime }} - {{ event.endTime }}
			</div>

			<div class="col-2">
				{{ event.location }}
			</div>

			<div class="col-2">
				{{event.eventType}}
			</div>

			<div class="col-2">

				<div v-if="event.description">
					{{ event.description }}
				</div>

				<div v-else>
					{{ event.summary }}
				</div>

			</div>

			<div class="col-2 text-right">
				
				<button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDataForm()">
	                    <i class="far fa-edit"></i>
	                </button>

	                <button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleCalendarDataForm()">
	                    <i class="fas fa-ellipsis-v"></i>
	                </button>
			</div>


			
		</div>
		
	</div>
</template>

<script>
	export default {
		props:['event'],

		data() {
            return {
            	// showBody: true
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

    //         toggleCalendarDataForm: function() {
    //             this.$parent.$refs.calendar.forEach((element) => {
				// 	element.showBody = false;
				// });
				// this.showBody = !this.showBody;
    //         }

        },

        mounted() {

        }
	}

</script>