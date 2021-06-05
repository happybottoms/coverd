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
            <div class="col-xs-4">
                <option-list
                    v-model="filters"
                    label="Source Location"
                    api-path="warehouses"
                    property="id"
                    display-property="title"
                    empty-string="-- All Locations --"
                />
            </div>
            <div class="col-xs-4">
                <option-list
                    v-model="filters"
                    label="Destination Location"
                    api-path="warehouses"
                    property="id"
                    display-property="title"
                    empty-string="-- All Locations --"
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
                            api-url="/api/orders/transfer"
                            edit-route="/orders/transfer/"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            link-display-property="sequence"
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
import OptionList from '../../../components/OptionListEntity.vue';
export default {
    components: {
        TablePaged,
        OptionListStatic,
        OptionList
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
