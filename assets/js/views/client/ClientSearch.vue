<template>
    <section class="content">
        <h3 class="box-title">
            Client Search
        </h3>

        <div class="row">
            <div class="col-xs-2">
                <TextField
                    v-model="filters.keyword"
                    label="Keyword"
                />
            </div>
            <div class="col-xs-4">
                <PartnerSelectionForm
                    v-model="filters.partner"
                    label="Assigned Partner"
                />
            </div>

            <div class="col-xs-3">
                <button
                    class="btn btn-success btn-flat"
                    @click="doFilter"
                >
                    <i class="fa fa-fw fa-filter" />
                    Filter
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <TableStatic
                            ref="hbtable"
                            :columns="columns"
                            api-url="/api/clients/search"
                            :sort-order="[{ field: 'id', direction: 'desc'}]"
                            :params="requestParams()"
                        />
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6 col-md-offset-3">
                <div class="callout callout-info">
                    <h4>Search is limited to 5 results</h4>
                    <p>Only 5 results are allowed at a time in this search. Please use the filter above to narrow the results</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import PartnerSelectionForm from "../../components/PartnerSelectionForm";
    import TextField from "../../components/TextField";
    import TableStatic from "../../components/TableStatic";

    export default {
        name: 'ClientSearch',
        components: {
            TableStatic,
            TextField,
            PartnerSelectionForm,
        },
        props: [],
        data() {
            return {
                columns: [
                    //todo: find a better way to sort value objects #30
                    { name: '__checkbox', title: "#" },
                    { name: 'id', title: "ID", sortField: 'c.id' },
                    { name: 'fullName', title: "Name", sortField: 'c.fullName' },
                    { name: 'partner.title', title: "Assigned Partner", sortField: 'partner.title'},
                    { name: 'status', title: "Status", callback: 'statusFormat', sortField: 'status' },
                ],
                clients: {},
                statuses: [
                    {id: "ACTIVE", name: "Active"},
                    {id: "INACTIVE", name: "Inactive"}
                ],
                filters: {
                    keyword: null,
                    partner: { id: null }
                },
                selection: [],
            }
        },
        created() {
            console.log('Component mounted.')
        },
        mounted() {
            this.$events.$on('selection-change', eventData => this.onSelectionChange(eventData));
        },
        methods: {
            routerLink: function (id) {
                return "<router-link :to=" + { name: 'client-edit', params: { id: id }} + "><i class=\"fa fa-edit\"></i> " + id + "</router-link>";
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
                }
            },
            refreshTable () {
                this.$refs.hbtable.refresh();
            },
            requestParams: function () {
                return {
                    status: this.filters.status || null,
                    keyword: this.filters.keyword || null,
                    partner: this.filters.partner.id || null,
                    include: ['partner'],
                }
            }
        },
    }
</script>