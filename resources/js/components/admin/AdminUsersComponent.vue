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

                <div class="col-lg-2 text-lg-right">
                    <input type="text" class="form-control" @keyup="searchUserByEmail($event)" placeholder="Find user by e-mail" @>
                </div>
            </div>
        </div>

<!--        <div class="filter-links">-->
<!--            <button v-for="typeFilter in calendarsTypesFilters" :key="typeFilter.title" @click="applyCalendarsTypeFilter(typeFilter)" type="button" class="btn btn-sm" :class="{ 'active': typeFilter.active }">{{ typeFilter.title }}</button>-->
<!--        </div>-->

        <div>


            <div v-if="Object.keys(users).length > 0" class="users-list">

                <div class="row p-3 px-md-4 mb-3">
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListById">
                                    ID
                                    <i v-if="sortByIdDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>

                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByName">
                                    Name
                                    <i v-if="sortByNameDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByEmail">
                                    E-mail
                                    <i v-if="sortByEmailDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                Is Admin
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByOwnCalendars">
                                    Own calendars
                                    <i v-if="sortByOwnCalendarsDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByPublicCalendars">
                                    Public calendars
                                    <i v-if="sortByPublicCalendarsDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link" href="javascript:void(0)" @click="sortUsersListByRole">
                                    Role
                                    <i v-if="sortByRoleDirection === 'desc'" class="fas fa-sort-amount-up-alt float-right"></i>
                                    <i v-else class="fas fa-sort-amount-down-alt float-right"></i>
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="user in sortedUsers" >
                            <td>{{ user.id }}</td>
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>
                                <div class="form-group form-check">
                                    <input :id="'user_'+user.id+'_role'" type="checkbox" class="form-check-input" :checked="user.role === 'admin'" :disabled="requestProcess" @change="setUserRole(user.id, $event)">
                                    <label class="form-check-label" :for="'user_'+user.id+'_role'">Admin</label>
                                </div>
                            </td>
                            <td>{{ user.ownCalendarsCount}}</td>
                            <td>{{ user.publicCalendarsCount}}</td>
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
            sortedUsers: [],
            requestDanger: '',
            requestSuccess: '',
            requestProcess: false,
            searchUserByEmailKeyword: '',

            sortByIdDirection: 'desc',
            sortByNameDirection: 'desc',
            sortByEmailDirection: 'desc',
            sortByOwnCalendarsDirection: 'desc',
            sortByPublicCalendarsDirection: 'desc',
            sortByRoleDirection: 'desc'
        }
    },

    created() {

    },

    mounted() {
        this.sortUsersListById();
    },

    methods: {
        setUserRole: function(userId, event) {
            let currentObj = this;
            axios.interceptors.request.use(function (config) {
                // Do something before request is sent
                currentObj.requestProcess = true;
                return config;
            }, function (error) {
                // Do something with request error
                return Promise.reject(error);
            });

            let role = event.target.checked ? 'admin' : 'user';
            axios.post('/admin-set-user-role', {user_id: userId, role: role})
            .then(function(response) {
                if (response.data.code === 401) {
                    document.location.href="/";
                } else if (response.data.code === 404) {
                    currentObj.requestDanger = response.data.data.message;
                } else if (response.data.code === 1) {
                    currentObj.requestSuccess = response.data.data.message;
                    setTimeout(function() {
                        currentObj.requestSuccess = false;
                    }, 2000);
                } else {
                    currentObj.requestDanger = 'Request Error';
                }
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422) {
                    currentObj.requestDanger = error.response.data.message;
                } else {
                    currentObj.requestDanger = 'Request Error';
                }
            })
            .then(function() {
                currentObj.requestProcess = false;
            });
        },

        searchUserByEmail: function(event) {
            let keyword = event.target.value;
            let searchResultArray = [];
            this.users.forEach(function(item, i, arr) {
                if (item.email.includes(keyword)) {
                    searchResultArray.push(item);
                }
            });
            this.sortedUsers = searchResultArray;
        },

        sortUsersListById: function() {
            this.sortByIdDirection = this.sortByIdDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'id', this.sortByIdDirection);
        },
        sortUsersListByName: function() {
            this.sortByNameDirection = this.sortByNameDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'name', this.sortByNameDirection);
        },
        sortUsersListByEmail: function() {
            this.sortByEmailDirection = this.sortByEmailDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'email', this.sortByEmailDirection);
        },
        sortUsersListByOwnCalendars: function() {
            this.sortByOwnCalendarsDirection = this.sortByOwnCalendarsDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'ownCalendarsCount', this.sortByOwnCalendarsDirection);
        },
        sortUsersListByPublicCalendars: function() {
            this.sortByPublicCalendarsDirection = this.sortByPublicCalendarsDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'publicCalendarsCount', this.sortByPublicCalendarsDirection);
        },
        sortUsersListByRole: function() {
            this.sortByRoleDirection = this.sortByRoleDirection === 'desc' ? 'asc' : 'desc';
            this.sortedUsers = this.sortArray(this.users, 'role', this.sortByRoleDirection);
        },
        sortArray: function(array, field, direction) {
            return _.orderBy(array, field, direction);
        }
    }
}
</script>
