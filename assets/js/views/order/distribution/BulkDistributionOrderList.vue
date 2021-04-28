<template>
    <section class="content">
        <router-link
            :to="{ name: 'order-distribution-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Partner Distribution
        </router-link>
        <h3 class="box-title">
            Partner Distributions List
        </h3>

        <!-- filter container -->
        <div class="row">
            <div class="col-xs-2">
                <datefield
                    v-model="filters.distributionMonth"
                    label="Distribution month"
                    format="YYYY-MM"
                    timezone="Etc/UTC"
                />
            </div>
            <div class="col-xs-3">
                <partnerselectionform
                    v-model="filters.partner"
                    label="Partner"
                    :options="allPartners"
                />
            </div>
            <div class="col-xs-2">
                <optionliststatic
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="filterStatuses"
                    empty-string="-- All Statuses --"
                />
                <!-- /.input group -->
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
                        <div class="col-xs-12">
                            <div class="btn-group">
                                <button
                                    type="button"
                                    class="btn btn-info btn-flat dropdown-toggle"
                                    data-toggle="dropdown"
                                    :disabled="selection.length == 0"
                                >
                                    <i class="fa fa-fw fa-wrench" />
                                    Bulk Operations ({{ selection.length }})
                                    <span class="caret" />
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul
                                    class="dropdown-menu"
                                    role="menu"
                                >
                                    <li
                                        v-for="status in statuses"
                                        :key="status.id"
                                    >
                                        <a @click="bulkStatusChange(status.id)">Change Status to <strong>{{ status.name }}</strong></a>
                                    </li>
                                    <li class="divider" />
                                    <li><a @click="bulkDelete()"><i class="fa fa-fw fa-trash" />Delete Distributions</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <tablepaged
                            ref="hbtable"
                            :columns="columns"
                            api-url="/api/orders/distribution"
                            edit-route="/orders/distribution/"
                            link-display-property="sequence"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            :params="requestParams()"
                            :per-page="50"
                        />
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
        <modalbulkchange
            :items="selection"
            item-type="Distributions"
            :action="this.doBulkChange"
        />
        <modalbulkdelete
            :items="selection"
            item-type="Distributions"
            :action="this.doBulkDelete"
            bulk-change-type="delete"
        />
    </section>
</template>

<script>
    import ModalConfirmBulkChange from '../../../components/ModalConfirmBulkChange.vue';
    import ModalConfirmBulkDelete from '../../../components/ModalConfirmBulkDelete.vue';
    import TablePaged from '../../../components/TablePaged.vue';
    import DateField from "../../../components/DateField.vue";
    import PartnerSelectionForm from "../../../components/PartnerSelectionForm.vue";
    import OptionListStatic from "../../../components/OptionListStatic.vue";
    import { mapGetters } from "vuex";

    export default {
        components: { 
            'modalbulkchange' : ModalConfirmBulkChange,
            'modalbulkdelete' : ModalConfirmBulkDelete,
            'tablepaged' : TablePaged,
            datefield: DateField,
            optionliststatic: OptionListStatic,
            partnerselectionform: PartnerSelectionForm,
        },
        props:[],
        data() {
            return {
                orders: {},
                columns: [
                    { name: '__checkbox', title: "#" },
                    { name: '__slot:link', title: "Order Id", sortField: 'id' },
                    { name: 'partner.title', title: "Partner", sortField: 'partner.title' },
                    { name: 'distributionPeriod', title: "Distribution Period", callback: 'periodFormat', sortField: 'distributionPeriod' },
                    { name: 'status', title: "Status", sortField: 'status' },
                    { name: 'createdAt', title: "Created", callback: 'dateTimeFormat', sortField: 'createdAt' },
                    { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
                ],
                statuses: [
                    {id: "COMPLETED", name: "Completed"},
                ],
                selection: [],
                filterStatuses: [
                    { id: "IN_PROCESS", name: "In Process" },
                    { id: "PENDING", name: "Pending" },
                    { id: "SHIPPED", name: "Shipped" }
                ],
                filters: {
                    status: null,
                    distributionMonth: null,
                    partner: {}
                },

            };
        },
        mounted() {
            this.$events.$on('selection-change', eventData => this.onSelectionChange(eventData));
        },
        computed: mapGetters(["allPartners"]),
        methods: {
            onSelectionChange (selection) {
                this.selection = selection;
            },
            bulkStatusChange (statusId) {
                $('#bulkChangeModal').modal('show');
                this.bulkChange = {
                    status: statusId
                };
            },
            doBulkChange () {
                let self = this;
                axios
                    .patch('/api/orders/bulk-distribution/bulk-change', {
                        ids: self.selection,
                        changes: self.bulkChange,
                    })
                    .then(response => self.$refs.hbtable.refresh())
                    .catch(function (error) {
                        console.log(error);
                    });

            },
            bulkDelete (statusId) {
                $('#bulkDeleteModal').modal('show');
                this.bulkChange = {
                    status: statusId
                };
            },
            doBulkDelete () {
                let self = this;
                axios
                    .patch('/api/orders/distribution/bulk-delete', {
                        ids: self.selection,
                    })
                    .then(response => {
                        self.$refs.hbtable.clearSelected();
                        self.$refs.hbtable.refresh();
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            doFilter() {
                console.log("doFilter:", this.requestParams());
                this.$events.fire("filter-set", this.requestParams());
            },
            requestParams: function() {
                return {
                    status: this.filters.status || null,
                    distributionMonth: this.filters.distributionMonth || null,
                    partner: this.filters.partner.id || null
                };
            }
        },
    }
</script>
