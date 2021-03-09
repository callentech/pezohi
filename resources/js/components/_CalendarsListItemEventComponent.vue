<template>
	<div class="card calendar-single">

		<div class="row">



			<div class="data-wrapper col-2">
				<div class="data">
					event.start.dateTime|formatDate('MMMM D, YYYY')
				</div>

				<!-- <div class="input-group input-group-sm data-input">
					<date-picker :config="dateOptions" name="event_datetime" placeholder="dd.mm.YYYY" required :value="event.start.dateTime|formatDate('MMMM D, YYYY')"></date-picker>
					<div class="input-group-append">
						<span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
					</div>
					<div class="invalid-feedback">Please provide a valid date.</div>
				</div> -->
			</div>


			<div class="col-2">
				 event.startTime  -  event.endTime
				<!-- <div class="form-group">
            <label for="date-time-input">Select date time (wrap)</label>
            <div class="input-group">
              <date-picker  id="date-time-input"
                           :wrap="true" :config="configs.wrap">
              </date-picker>
              <div class="input-group-append">
                <button class="btn btn-secondary datepickerbutton" type="button" title="Toggle">
                  <i class="far fa-calendar"></i>
                </button>
              </div>
            </div>
          </div> -->

         <!--  <div class="form-group">
            <label>Select time</label>
            <div class="input-group">
              <date-picker :config="configs.timePicker" :wrap="true"
                           placeholder="Time"></date-picker>
              <div class="input-group-append">
                <button class="btn btn-primary datepickerbutton" type="button" title="Toggle">
                  <i class="far fa-clock"></i>
                </button>
              </div>
            </div>
          </div> -->


			</div>

			<div class="col-2">
				<div class="data">
					event.location
				</div>
<!--
				<div class="data-input">
					<input type="text" name="event_address" class="form-control form-control-sm" placeholder="Address" :value="event.location" required >
					<div class="invalid-feedback">Please provide a valid address.</div>
				</div> -->
			</div>

			<div class="col-2">
				<div  vif="typeof event.extendedProperties !== 'undefined' && typeof event.extendedProperties.private.type !== 'undefined'">
					event.extendedProperties.private.type | capitalize

					<!-- <div class="data-input">
						<select name="event_type" class="form-control form-control-sm" required>
							<option value="" disabled selected>Select type</option>
							<option value="game" :selected="event.extendedProperties.private.type === 'game'">Game</option>
							<option value="practice" :selected="event.extendedProperties.private.type === 'practice'">Practice</option>
						</select>
						<div class="invalid-feedback">Please provide a valid type.</div>
					</div> -->

				</div>

				<div v-else>
					<!-- <div class="data-input">
						<select name="event_type" class="form-control form-control-sm" required>
							<option value="" disabled selected>Select type</option>
							<option value="game">Game</option>
							<option value="practice">Practice</option>
						</select>
						<div class="invalid-feedback">Please provide a valid type.</div>
					</div> -->
				</div>


			</div>



			<div class="col-2">

				<div class="data">
					event.description
				</div>

				<!-- <div class="data-input">
					<input type="text" name="event_notes" class="form-control form-control-sm" placeholder="e.g. Instructions" :value="event.description" required>
					<div class="invalid-feedback">Please provide a valid notes.</div>
				</div> -->

			</div>

			<div class="col-2 text-right">

				<button type="button" class="btn btn-light btn-sm pull-right btn-open" @click="toggleEditEventDataForm()">
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

	import datePicker from 'vue-bootstrap-datetimepicker';
	import DateRangePicker from 'vue2-daterange-picker'

	import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
	import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

    // Using font-awesome 5 icons
    import '@fortawesome/fontawesome-free/css/fontawesome.css';
    import '@fortawesome/fontawesome-free/css/regular.css';
    import '@fortawesome/fontawesome-free/css/solid.css';

    jQuery.extend(true, jQuery.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });


	export default {

        props:['event']

/*
		props:['event'],

		components: {
        	datePicker,
        	DateRangePicker
        },



		data() {
            return {

            	dateOptions: {
			    	format: 'DD.MM.YYYY',
			        useCurrent: true
			    },

			    timeOptions: {
		            format: 'LT',
		            useCurrent: false
		        },


		        configs: {
          basic: {
            // https://momentjs.com/docs/#/displaying/format/
            format: 'DD/MM/YYYY'
          },
          wrap: {
            allowInputToggle: true
          },
          timePicker: {
            format: 'LT',
            useCurrent: false
          },
          locale: {
            // https://github.com/moment/moment/tree/develop/locale
            locale: 'hi',
          },
          inline: {
            format: 'LLL',
            inline: true,
            sideBySide: true
          },
          start: {
            format: 'DD/MM/YYYY',
            useCurrent: false,
            showClear: true,
            showClose: true,

            maxDate: false
          },
          end: {
            format: 'DD/MM/YYYY',
            useCurrent: false,
            showClear: true,
            showClose: true,

          }
        },




            	// showBody: true
                // calendars: this.data,
                // calendarsTypesFilters: [
                //     { title: 'All calendars', val: 'all', active: true },
                //     { title: 'Owned by me', val: 'owned', active: false},
                //     { title: 'Shared with me', val: 'shared', active: false}
                // ]
            }
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
          let day = date.getDate() >= 10 ? date.getDate() : '0'+date.getDate();
  				return day+'.'+month+'.'+date.getFullYear();
  			}

		},

        methods: {

        	toggleEditEventDataForm: function() {
        		alert();
        	}
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

        */
	}

</script>
