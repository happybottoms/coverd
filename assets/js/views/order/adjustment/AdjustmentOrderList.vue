<template>
    <section class="content">
        <router-link
            :to="{ name: 'order-adjustment-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Stock Change
        </router-link>
        <h3 class="box-title">
            Stock Changes List
        </h3>
        <div class="row">
            <div class="col-xs-3">
                <storage-location-selection-form v-model="filters.source" label="Source Location" />
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
                            api-url="/api/orders/adjustment"
                            edit-route="/orders/adjustment/"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            link-display-property="sequence"
                            :params="requestParams()"
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
    import StorageLocationSelectionForm from '../../../components/StorageLocationSelectionForm';
    import OptionListStatic from '../../../components/OptionListStatic.vue';

    export default {
        components: {
            TablePaged,
            StorageLocationSelectionForm,
            OptionListStatic
        },
        props:[],
        data() {
            return {
                orders: {},
                columns: [
                    { name: '__checkbox', title: "#" },
                    { name: '__slot:link', title: "Order", sortField: 'sequenceNo' },
                    { name: 'storageLocation.title', title: "Storage Location", sortField: 'storageLocation.title' },
                    { name: 'status', title: "Status", sortField: 'status' },
                    { name: 'createdAt', title: "Created", callback: 'dateTimeFormat', sortField: 'createdAt' },
                    { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
                ],
                filters: {
                    status: null,
                    source: {}
                },
                statuses: [
                    { id: "CREATING", name: "Creating" },
                    { id: "COMPLETED", name: "Completed" }
                ],
            };
        },
        methods: {
            doFilter () {
                this.$events.fire('filter-set', this.requestParams());
            },
            requestParams() {
                return {
                    status: this.filters.status || null,
                    storageLocation: this.filters.source.id || null,
                }
            },
        }
    }
</script>
