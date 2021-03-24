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

// Backend Components
Vue.component('all-calendars-component', require('./components/AllCalendarsComponent.vue').default);
Vue.component('calendars-list-item-component', require('./components/CalendarsListItemComponent.vue').default);
Vue.component('calendars-list-item-event-component', require('./components/CalendarsListItemEventComponent.vue').default);

Vue.component('add-edit-calendar-modal-component', require('./components/AddEditCalendarModalComponent.vue').default);

Vue.component('event-data-edit-component', require('./components/EventDataEditComponent.vue').default);

// Frontend Components
Vue.component('frontend-calendar-data-component', require('./components/frontend/CalendarDataComponent.vue').default);

// Admin components
Vue.component('admin-calendars-component', require('./components/admin/AdminCalendarsComponent.vue').default);
Vue.component('admin-users-component', require('./components/admin/AdminUsersComponent.vue').default);
Vue.component('admin-calendars-list-item-component', require('./components/admin/AdminCalendarsListItemComponent.vue').default);





/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',

    data() {
    	return {

            showAdminUsers: true,
            showAdminCalendars: false

    	}
    },

    methods: {
        showAdminUsersAction: function() {
            this.showAdminUsers = true;
            this.showAdminCalendars = false;
        },

        showAdminCalendarsAction: function() {
            this.showAdminUsers = false;
            this.showAdminCalendars = true;
        }

    }
});
