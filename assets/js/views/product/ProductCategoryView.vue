<template>
    <section class="content">
        <router-link
            :to="'/' + apiPath + '/new'"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create {{ name }}
        </router-link>
        <h3 class="box-title">
            {{ name }} List
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
                                    <th>{{ name }} ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Partner Orderable</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="listOption in listOptions.data"
                                    :key="listOption.id"
                                >
                                    <td>
                                        <router-link :to="'/' + apiPath + '/' + listOption.id">
                                            <i class="fa fa-edit" />{{ listOption.id }}
                                        </router-link>
                                    </td>
                                    <td v-text="listOption.name" />
                                    <td v-text="listOption.status" />
                                    <td v-text="listOption.isPartnerOrderable" />
                                    <td>{{ listOption.updatedAt | dateTimeFormat }}</td>
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
        props:['name','apiPath'],
        data() {
            return {
                listOptions: {},
                loading: false,
            };
        },
        created() {
            this.loading = true;
            axios
                .get('/api/' + this.apiPath)
                .then(response => {
                    this.listOptions = response.data;
                     this.loading = false;
                })
                .catch(error => {
                    console.log(error)
                });
            console.log('Component mounted.')
        }
    }
</script>