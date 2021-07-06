<template>
    <div class="box" style="min-height:500px">
        <div class="overlay" v-if="loading"><i class="fa fa-sync fa-spin" /></div>
        <div :class="[{'vuetable-wrapper ui basic segment': true}, 'box-body table-responsive no-padding']" style="min-height:500px">
            <vuetable
                ref="vuetable"
                :api-url="apiUrl"
                :http-method="httpMethod"
                data-path="data"
                :fields="columns"
                :css="{
                    tableClass: 'table table-hover',
                    ascendingIcon: 'fa fa-sort-amount-up',
                    descendingIcon: 'fa fa-sort-amount-down'
                }"
                :sort-order="sortOrder"
                :append-params="params"
                track-by="id"
                @vuetable:checkbox-toggled="onCheckboxToggled"
                @vuetable:checkbox-toggled-all="onCheckboxToggled"
                @vuetable:loading="showLoader"
                @vuetable:loaded="hideLoader"
            >
                <template
                    slot="link"
                    slot-scope="props"
                >
                    <router-link :to="editRoute + props.rowData.id">
                        <i class="fa fa-edit" /> {{ props.rowData[linkDisplayProperty] }}
                    </router-link>
                </template>
                <template v-slot:actions="{rowData}">
                    <slot
                        name="actions"
                        :rowData="rowData"
                    />
                </template>
            </vuetable>
        </div>
    </div>
</template>

<script>
    import Vuetable from 'vuetable-2/src/components/Vuetable.vue'

    export default {
        name: "TableStatic",
        components: {
            Vuetable,
        },
        props:{
            columns: { type: Array, required: true },
            apiUrl: { type: String, required: true },
            httpMethod: { type: String, default: 'get' },
            editRoute: { type: String },
            sortOrder: { type: Array },
            params: { type: Object },
            linkDisplayProperty: { type: String, default: 'id' }
        },
        data () {
            return {
                loading: false,
            }
        },
        mounted() {
            this.$events.$on('filter-set', eventData => this.onFilterSet(eventData));
            console.log(this.loading);
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