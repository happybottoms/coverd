<template>
    <section class="content">
        <router-link
            :to="{ name: 'warehouse-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Warehouse
        </router-link>
        <h3 class="box-title">
            Warehouse List
        </h3>
        <div class="row">
            <div class="col-xs-4">
                <option-list
                    v-model="filters"
                    label="Warehouse Name"
                    api-path="warehouses"
                    property="id"
                    display-property="title"
                    empty-string="-- All Warehouses --"
                />
            </div>
            <div class="col-xs-4">
                <option-list-static
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="statuses"
                    empty-string="-- All Statuses --"
                />
            </div>
            <div class="col-xs-4">
                <button
                    class="btn btn-success btn-flat"
                    @click="doFilter"
                >
                    <i class="fa fa-fw fa-filter" />Filter
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
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
                        <table-paged
                            :columns="columns"
                            api-url="/api/warehouses"
                            edit-route="warehouses"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            :params="requestParams()"
                            :per-page="10"
                        />
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>
<script>
import TablePaged from '../../components/TablePaged.vue';
import OptionListStatic from '../../components/OptionListStatic.vue';
import OptionList from '../../components/OptionListEntity.vue';

export default {
    components: {
        TablePaged,
        OptionListStatic,
        OptionList
    },
    props:[],
    data() {
        return {
            warehouses: {},
            loading: true,
            columns: [
                { name: '__checkbox', title: "#" },
                { name: '__slot:link', title: "Warehouse Id", sortField: 'id' },
                { name: 'title', title: "Title", sortField: 'title' },
                { name: 'status', title: "Status", sortField: 'status' },
                { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
            ],
            statuses: [
                { id: "ACTIVE", name: "Active" },
                { id: "INACTIVE", name: "Inactive" }
            ],
            filters: {
                status: null,
                id: null
            },
        };
    },
    methods: {
        doFilter () {
            console.log('doFilter:', this.requestParams(), this.filters);
            this.$events.fire('filter-set', this.requestParams());
        },
        requestParams: function () {
            return {
                status: this.filters.status || null,
                id: this.filters.id || null,
            }
        },
    }
}
</script>
