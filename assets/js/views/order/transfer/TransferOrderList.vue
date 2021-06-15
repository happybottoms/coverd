<template>
    <section class="content">
        <router-link
            :to="{ name: 'order-transfer-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Transfer Order
        </router-link>
        <h3 class="box-title">
            Transfer Orders Lists
        </h3>
        <div class="row">
            <div class="col-xs-3">
                <storage-location-selection-form v-model="filters.source" label="Source Location" />
            </div>
            <div class="col-xs-3">
                <storage-location-selection-form v-model="filters.target" label="Target Location" />
            </div>
            <div class="col-xs-3">
                <option-list-static
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="statuses"
                    empty-string="-- All Statuses --"
                />
            </div>
            <div class="col-xs-3">
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
                            api-url="/api/orders/transfer"
                            edit-route="/orders/transfer/"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            link-display-property="sequence"
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
import TablePaged from '../../../components/TablePaged.vue';
import OptionListStatic from '../../../components/OptionListStatic.vue';
import StorageLocationSelectionForm from '../../../components/StorageLocationSelectionForm';

export default {
    components: {
        TablePaged,
        OptionListStatic,
        StorageLocationSelectionForm
    },
    data() {
        return {
            orders: {},
            columns: [
                { name: '__checkbox', title: "#" },
                { name: '__slot:link', title: "Order Id", sortField: 'id' },
                { name: 'sourceLocation.title', title: "Source Location", sortField: 'sourceLocation.title' },
                { name: 'targetLocation.title', title: "Target Location", sortField: 'targetLocation.title' },
                { name: 'status', title: "Status", sortField: 'status' },
                { name: 'createdAt', title: "Created", callback: 'dateTimeFormat', sortField: 'createdAt' },
                { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
            ],
            filters: {
                status: null,
                source: {},
                target: {}
            },
            statuses: [
                { id: "CREATING", name: "Creating" },
                { id: "COMPLETED", name: "Completed" }
            ],
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
                sourceLocation: this.filters.source.id || null,
                targetLocation: this.filters.target.id || null,
            }
        },
    }
}
</script>
