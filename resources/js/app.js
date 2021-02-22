/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

Vue.component('all-calendars-component', require('./components/AllCalendarsComponent.vue').default);
Vue.component('calendars-list-item-component', require('./components/CalendarsListItemComponent.vue').default);
Vue.component('calendars-list-event-component', require('./components/CalendarsListItemEventComponent.vue').default);



Vue.component('add-edit-calendar-modal-component', require('./components/AddEditCalendarModalComponent.vue').default);





/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',

    data() {
    	return {
    	// 	modal_data: {
    	// 		title: '',
    	// 		form_action: '',
    	// 		calendarData: null
    	// 	}
    		
    	}
    },

    methods: {
  //   	showAddCalendarModal: function() {
		// 	this.modal_data.title = 'Add Calendar';
		// 	this.modal_data.form_action = '/calendar-new';
		// 	jQuery('#addEditCalendarModal').modal('show');
		// },

		// showEditCalendarModal: function(calendarId) {

		// 	let currentObj = this;

		// 	axios.post('/calendar-get-data', { calendar_id: calendarId })
			
		// 		.then(function (response) {

		// 			console.log(response.data);

		// 			currentObj.modal_data.calendarData = response.data.data.calendarData;

		// 			// if (response.data.code == 1) {
		// 			// 	currentObj.requestSuccess = 'Success create calendar';
		// 			// 	currentObj.addCalendarResetForm();
		// 			// 	setTimeout(function() {
		// 			// 		currentObj.requestSuccess = false;
		// 			// 		jQuery('#addCalendarModal').modal('hide');
		// 			// 		location.reload();
		// 			// 	}, 3000);
		// 			// } else {
		// 			// 	currentObj.requestDanger = 'Error Request';
		// 			// }
		// 		})
		// 		.catch(function (error) {
		// 			// if (error.response.status == 422) {
		// 			// 	currentObj.requestDanger = error.response.data.message;
		// 			// 	form.classList.add('was-validated');
		// 			// } else {
		// 			// 	currentObj.requestDanger = 'Error Request';
		// 			// }
		// 		})
		// 		.then(function() {
		// 			//currentObj.formRequestProcess = false;
		// 			// currentObj.inputDisabled = false;
		// 	});





		// 	this.modal_data.title = 'Edit Calendar';
		// 	this.modal_data.form_action = '/calendar-edit';

		// 	//this.modal_data.eventData = [];
			





		// 	jQuery('#addEditCalendarModal').modal('show');


		// }
    }
});
