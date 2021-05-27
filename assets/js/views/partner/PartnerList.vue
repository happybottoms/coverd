<template>
    <section class="content">
        <router-link
            :to="{ name: 'partner-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Partner
        </router-link>
        <h3 class="box-title">
            Partner List
        </h3>
        <div class="row">
            <div class="col-xs-2">
                <option-list
                    v-model="filters"
                    label="Partner Name"
                    api-path="partners"
                    property="id"
                    display-property="title"
                    empty-string="-- All Partners --"
                />
            </div>
            <div class="col-xs-2">
                <option-list-static
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="statuses"
                    empty-string="-- All Statuses --"
                />
            </div>
            <div class="col-xs-2">
                <option-list-static
                    v-model="filters.partnerType"
                    label="Type"
                    :preloaded-options="types"
                    empty-string="-- All Types --"
                />
            </div>
            <div class="col-xs-2">
                <option-list
                    v-model="filters"
                    label="Partner Fulfillment Period"
                    api-path="partners/fulfillment-periods"
                    property="fulfillmentPeriod"
                    display-property="name"
                    empty-string="-- All Periods --"
                />
            </div>
            <div class="col-xs-2">
                <option-list
                    v-model="filters"
                    label="Distribution Method"
                    api-path="partners/distribution-methods"
                    property="distributionMethod"
                    display-property="name"
                    empty-string="-- All Methods --"
                />
            </div>
            <div class="col-xs-2">
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
                    <div class="box-body table-responsive no-padding">
                        <table-paged
                            :columns="columns"
                            :params="requestParams()"
                            api-url="/api/partners"
                            edit-route="partners/"
                        />
                    </div>
                </div>
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
        data() {
            return {
                columns: [
                    { name: '__checkbox', title: "#" },
                    { name: '__slot:link', title: "Partner Id", sortField: 'id' },
                    { name: 'title', title: "Title", sortField: 'title' },
                    { name: 'partnerType', title: "Type", sortField: 'p.partnerType' },
                    { name: 'status', title: "Status", sortField: 'status' },
                    { name: 'fulfillmentPeriod.name', title: "Fulfillment Period", sortField: 'fulfillmentPeriod' },
                    { name: 'distributionMethod.name', title: "Distribution Method", sortField: 'distributionMethod' },
                    { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
                ],
                filters: {
                    status: null,
                    id: null,
                    partnerType: null,
                    fulfillmentPeriod: null,
                    distributionMethod: null
                },
                statuses: [
                    { id: "ACTIVE", name: "Active" },
                    { id: "APPLICATION_PENDING_PRIORITY", name: "Application Pending Priority" },
                    { id: "INACTIVE", name: "Inactive" },
                    { id: "START", name: "Start" },
                ],
                types: [
                    { id: "HOSPITAL", name: "Hospital" },
                    { id: "AGENCY", name: "Agency" },
                ],
                periods: [
                    { id: "HOSPITAL", name: "Hospital" },
                    { id: "AGENCY", name: "Agency" },
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
                id: this.filters.id || null,
                partnerType: this.filters.partnerType || null,
                fulfillmentPeriod: this.filters.fulfillmentPeriod || null,
                distributionMethod: this.filters.distributionMethod || null,
            }
        },
    }
    }
</script>
