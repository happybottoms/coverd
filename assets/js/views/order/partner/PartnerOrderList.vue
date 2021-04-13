<template>
    <section class="content">
        <router-link
            :to="{ name: 'order-partner-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Partner Order
        </router-link>
        <h3 class="box-title">
            Partner Orders List
        </h3>

        <div class="row">
            <div class="col-xs-2">
                <datefield
                    v-model="filters.orderPeriod"
                    label="Order Month"
                    format="YYYY-MM-01"
                    timezone="Etc/UTC"
                />
            </div>
            <div class="col-xs-3">
                <partnerselectionform
                    v-model="filters.partner"
                    label="Partner"
                />
            </div>
            <div class="col-xs-2">
                <optionlist
                    v-model="filters"
                    label="Partner Fulfillment Period"
                    api-path="partners/fulfillment-periods"
                    property="fulfillmentPeriod"
                    display-property="name"
                    empty-string="-- All Periods --"
                />
            </div>

            <div class="col-xs-2">
                <optionliststatic
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="statuses"
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

        <div class="row" />
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
                                    <li>
                                        <router-link :to="'/orders/partner/bulk-fill-sheet/' + selection.join(',')">
                                            <i class="fa fa-print fa-fw" />Print Fill Sheets
                                        </router-link>
                                    </li>
                                    <!--<li><a href="#">Separated link</a></li>-->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <tablepaged
                            ref="hbtable"
                            :columns="columns"
                            api-url="/api/orders/partner"
                            edit-route="/orders/partner/"
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
            item-type="Orders"
            :action="this.doBulkChange"
        />
    </section>
</template>

<script>
import ModalConfirmBulkChange from '../../../components/ModalConfirmBulkChange.vue';
import DateField from '../../../components/DateField.vue';
import OptionListEntity from '../../../components/OptionListEntity.vue';
import OptionListStatic from '../../../components/OptionListStatic.vue';
import PartnerSelectionForm from '../../../components/PartnerSelectionForm.vue';
import TablePaged from '../../../components/TablePaged.vue';
export default {
    components: {
        'modalbulkchange' : ModalConfirmBulkChange,
        'datefield' : DateField,
        'optionlist' : OptionListEntity,
        'optionliststatic' : OptionListStatic,
        'partnerselectionform' : PartnerSelectionForm,
        'tablepaged' : TablePaged
    },
    props:[],
    data() {
        return {
            orders: {},
            columns: [
                { name: '__checkbox', title: "#" },
                { name: '__slot:link', title: "Order Id", sortField: 'id' },
                { name: 'partner.title', title: "Partner", sortField: 'partner.title' },
                { name: 'warehouse.title', title: "Warehouse", sortField: 'warehouse.title' },
                { name: 'orderPeriod', title: "Order Period", callback: 'periodFormat', sortField: 'orderPeriod' },
                { name: 'status', title: "Status", sortField: 'status' },
                { name: 'createdAt', title: "Created", callback: 'dateTimeFormat', sortField: 'createdAt' },
                { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
            ],
            statuses: [
                { id: "IN_PROCESS", name: "In Process" },
                { id: "PENDING", name: "Pending" },
                { id: "SHIPPED", name: "Shipped" },
            ],
            filters: {
                status: null,
                fulfillmentPeriod: null,
                orderPeriod: null,
                partner: {}
            },
            selection: [],
            bulkChange: {},
        };
    },
    mounted() {
        this.$events.$on('selection-change', eventData => this.onSelectionChange(eventData));
    },
    methods: {
        routerLink: function (id) {
            return "<router-link to=" + { name: 'order-partner-edit', params: { id: id }} + "><i class=\"fa fa-edit\"></i>" + id + "</router-link>";
        },
        onPaginationData (paginationData) {
            this.$refs.pagination.setPaginationData(paginationData)
        },
        onChangePage (page) {
            this.$refs.vuetable.changePage(page)
        },
        doFilter () {
            console.log('doFilter:', this.requestParams());
            this.$events.fire('filter-set', this.requestParams());
        },
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
                .patch('/api/orders/partner/bulk-change', {
                    ids: self.selection,
                    changes: self.bulkChange,
                })
                .then(response => self.$refs.hbtable.refresh())
                .catch(function (error) {
                    console.log(error);
                });

        },
        requestParams: function () {
            return {
                status: this.filters.status || null,
                fulfillmentPeriod: this.filters.fulfillmentPeriod || null,
                orderPeriod: this.filters.orderPeriod || null,
                partner: this.filters.partner.id || null,
            }
        },
    }
}
</script>
