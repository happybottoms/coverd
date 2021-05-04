<template>
    <div :class="[{'vuetable-wrapper ui basic segment': true}, 'box-body table-responsive no-padding']">
        <div class="loadingArea">
            <pulse-loader
                :loading="loading"
                color="#3c8dbc"
            />
        </div>
        <div class="row">
            <div class="col-xs-6">
                <slot
                    name="toolbar-left"
                />
            </div>
            <div class="col-xs-6 text-right">
                <slot
                    name="toolbar-right"
                />
            </div>
        </div>
        <vuetable
            ref="vuetable"
            :api-url="apiUrl"
            data-path="data"
            pagination-path="meta.pagination"
            :fields="columns"
            :per-page="perPage"
            :css="{
                tableClass: 'table table-hover',
                ascendingIcon: 'fa fa-sort-amount-up',
                descendingIcon: 'fa fa-sort-amount-down'
            }"
            :sort-order="sortOrder"
            :append-params="params"
            track-by="id"
            @vuetable:pagination-data="onPaginationData"
            @vuetable:checkbox-toggled="onCheckboxToggled"
            @vuetable:checkbox-toggled-all="onCheckboxToggled"
            @vuetable:loading="showLoader"
            @vuetable:loaded="hideLoader"
        >
            <template v-slot:link="{rowData}">
                <router-link :to="editRoute + rowData[linkIdProperty]">
                    <i class="fa fa-edit" /> {{ rowData[linkDisplayProperty] }}
                </router-link>
            </template>
            <template v-slot:actions="{rowData}">
                <slot
                    name="actions"
                    :rowData="rowData"
                />
            </template>
        </vuetable>
        <div class="box-footer">
            <vuetable-pagination-info
                ref="paginationInfo"
                :css="{
                    infoClass: 'pull-left'
                }"
            />
            <vuetable-pagination
                ref="pagination"
                :css="{
                    wrapperClass: 'pagination pull-right',
                    activeClass: 'active large',
                    disabledClass: 'disabled',
                    pageClass: 'item',
                    linkClass: 'icon item',
                    paginationClass: 'ui bottom attached segment grid',
                    paginationInfoClass: 'left floated left aligned six wide column',
                    dropdownClass: 'ui search dropdown',
                    icons: {
                        first: 'fa fa-angle-double-left',
                        prev: 'fa fa-angle-left',
                        next: 'fa fa-angle-right',
                        last: 'fa fa-angle-double-right',
                    }
                }"
                @vuetable-pagination:change-page="onChangePage"
            />
        </div>
    </div>
</template>

<script>
    import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
    import Vuetable from 'vuetable-2/src/components/Vuetable.vue'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination.vue'
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo.vue'

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
            PulseLoader,
        },
        props:{
            columns: { type: Array, required: true },
            apiUrl: { type: String, required: true },
            editRoute: { type: String },
            sortOrder: { type: Array },
            params: { type: Object },
            perPage: { type: Number, default: 10 },
            linkDisplayProperty: { type: String, default: 'id' },
            linkIdProperty: { type: String, default: 'id' }
        },
        data () {
            return {
                loading: false,
            }
        },
        mounted() {
            this.$events.$on('filter-set', eventData => this.onFilterSet(eventData));
        },
        methods: {
            showLoader () {
                this.loading = true;
                console.log('show loader');
            },
            hideLoader () {
                this.loading = false;
                console.log('hide loader');
            },
            refresh() {
                Vue.nextTick( () => this.$refs.vuetable.refresh() );
            },
            reinitializeFields() {
                this.$nextTick(() => {
                    this.$refs.vuetable.normalizeFields();
                });
            },
            dateTimeFormat: function (date) {
                let d = moment(date);
                if (!d.isValid(date)) return null;
                return d.format('MM/DD/YYYY h:mm a');
            },
            dateFormat: function (date) {
                let d = moment(date).tz('Etc/UTC');
                if (!d.isValid(date)) return null;
                return d.format('MM/DD/YYYY');
            },
            periodFormat: function (date) {
                let d = moment(date).tz('Etc/UTC');
                if (!d.isValid(date)) return null;
                return d.format('YYYY-MM');
            },
            statusFormat: function (status) {
                if (status == null) return null;

                return status
                    .toLowerCase()
                    .split('_')
                    .map(upperStatus => upperStatus.charAt(0).toUpperCase() + upperStatus.slice(1))
                    .join(' ');
            },
            onPaginationData (paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
                this.$refs.paginationInfo.setPaginationData(paginationData);
            },
            onChangePage (page) {
                this.$refs.vuetable.changePage(page)
            },
            onFilterSet (filters) {
                this.params = filters;
                this.clearSelected();
                this.refresh();
                console.log('filter-set', filters);
            },
            onCheckboxToggled () {
                this.$events.fire('selection-change', this.$refs.vuetable.selectedTo);
            },
            clearSelected () {
                this.$refs.vuetable.selectedTo = [];
                this.$events.fire('selection-change', this.$refs.vuetable.selectedTo);
            },
        }
    }
</script>