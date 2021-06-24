<template>
    <section class="content">
        <router-link
            :to="{ name: 'admin-user-new'}"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create User
        </router-link>
        <h3 class="box-title">
            Users List
        </h3>

        <div class="row">
            <div class="col-xs-12">
                <div class="box" style="min-height:500px">
                <div class="overlay" v-if="loading"><i class="fa fa-sync fa-spin" /></div>
                    <div class="box-header">
                        <!--
                        <div class="box-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>User Email</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Groups</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                >
                                    <td>
                                        <router-link :to="{ name: 'admin-user-edit', params: { id: user.id }}">
                                            <i class="fa fa-edit" />{{ user.id }}
                                        </router-link>
                                    </td>
                                    <td v-text="user.email" />
                                    <td v-text="user.name.firstName" />
                                    <td v-text="user.name.lastName" />
                                    <td>
                                        <ul class="bulletless-list">
                                            <li
                                                v-for="group in user.groups"
                                                :key="group.id"
                                            >
                                                {{ group.name }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td>{{ user.updatedAt | dateTimeFormat }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                users: {
                    groups: []
                },
                groups: [],
                loading: false,
            };
        },
        created() {
            this.loading = true;
            axios
                .get('/api/users', { params: { include: ['groups'] }})
                .then(response => {
                    this.users = response.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.log(error)
                });
        }
    }
</script>

<style scoped>
    .bulletless-list {
        display: inline;
        list-style: none;
        margin: 0;
        padding: 0;
    }
</style>
