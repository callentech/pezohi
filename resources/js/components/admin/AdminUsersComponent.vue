<template>
    <div class="content-wrapper">
        <div class="page-head">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <h1>Users</h1>
                    <p class="">{{ users.length }} users</p>
                </div>

                <div class="col-lg-8">
                    <div v-if="requestDanger" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ requestDanger }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div v-if="requestSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ requestSuccess }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

<!--                <div class="col-lg-2 text-lg-right">-->
<!--                    <button type="button" class="btn btn-primary" @click="showAddCalendarModal"><i class="fa fa-plus"></i>Add Calendar</button>-->
<!--                </div>-->
            </div>
        </div>

<!--        <div class="filter-links">-->
<!--            <button v-for="typeFilter in calendarsTypesFilters" :key="typeFilter.title" @click="applyCalendarsTypeFilter(typeFilter)" type="button" class="btn btn-sm" :class="{ 'active': typeFilter.active }">{{ typeFilter.title }}</button>-->
<!--        </div>-->

        <div>


            <div v-if="Object.keys(users).length > 0" class="users-list">

                <div class="row p-3 px-md-4 mb-3">
                    <table class="table table-bordered calendar-events-table">
                        <thead>
                        <tr>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    Name
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    E-mail
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                Is Admin
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    Own calendars
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    Public calendars
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    Role
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in  users" >
                            <td>{{ user.name }}</td>
                            <td> {{ user.email }}</td>
                            <td>

                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" :checked="user.role === 'admin'">
                                    <label class="form-check-label">Admin</label>
                                </div>

                            </td>
                            <td>{{ user.calendars.length}}</td>
                            <td>{{ user.calendars.length}}</td>
                            <td>{{ user.role}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else class="calendars-list">
                <div class="alert alert-info" role="alert">
                    Users not found ...
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
            users: this.data,
            requestDanger: '',
            requestSuccess: '',

            sortByNameDirection: 'desc'
        }
    },

    created() {

    },

    methods: {
        sortUsersListByName: function() {

        }
    }
}
</script>
