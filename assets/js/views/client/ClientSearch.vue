<template>
    <section class="content">
        <h3 class="box-title">
            Client Search
        </h3>

        <div class="row">
            <div class="col-xs-3">
                <TextField
                    v-model="filters.keyword"
                    label="Name (Client and Parent/Guardian)"
                />
            </div>
            <div class="col-xs-2">
                <DateField
                    v-model="filters.birthdate"
                    label="Birthdate"
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
                        >
                            <template v-slot:actions="{rowData}">
                                <button
                                    v-tooltip
                                    :disabled="!userActivePartner || !rowData.canPartnerTransfer"
                                    :title="transferButtonTooltip(rowData)"
                                    class="btn btn-xs btn-primary"
                                    @click="onTransferClicked(rowData)"
                                >
                                    <i class="fa fa-exchange-alt fa-fw" />
                                    Transfer to Active Partner
                                </button>
                            </template>
                        </TableStatic>
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
        <ClientTransferModal
            :client="transferClient"
            :target-partner="userActivePartner || null"
        />
    </section>
</template>

<script>
    import PartnerSelectionForm from "../../components/PartnerSelectionForm";
    import TextField from "../../components/TextField";
    import TableStatic from "../../components/TableStatic";
    import DateField from "../../components/DateField";
    import ClientTransferModal from "./ClientTransferModal";
    import {mapGetters} from "vuex";

    export default {
        name: 'ClientSearch',
        components: {
            ClientTransferModal,
            DateField,
            TableStatic,
            TextField,
            PartnerSelectionForm,
        },
        props: [],
        data() {
            return {
                columns: [
                    { name: 'id', title: "ID", sortField: 'c.id' },
                    { name: 'fullName', title: "Name", sortField: 'c.fullName' },
                    { name: 'parentFullName', title: "Parent/Guardian" },
                    { name: 'birthdate', title: "Birthday", callback: 'dateFormat', sortField: 'c.birthdate' },
                    { name: 'partner.title', title: "Assigned Partner", sortField: 'partner.title' },
                    { name: 'status', title: "Status", callback: 'statusFormat', sortField: 'status' },
                    { name: '__slot:actions'}
                ],
                clients: {},
                statuses: [
                    {id: "ACTIVE", name: "Active"},
                    {id: "INACTIVE", name: "Inactive"}
                ],
                filters: {
                    keyword: null,
                    partner: { id: null },
                    birthdate: null
                },
                selection: [],
                transferClient: {}
            }
        },
        computed: {
            ...mapGetters([
                'userActivePartner'
            ])
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
                    birthdate: this.filters.birthdate || null,
                    include: ['partner'],
                }
            },
            onTransferClicked: function(client) {
                this.transferClient = client;
                $('#clientTransferModal').modal('show');
            },
            transferButtonTooltip: function(rowData) {
                if(this.userActivePartner && rowData.canPartnerTransfer) {
                    return 'Transfer client to ' + this.userActivePartner.title;
                }
                return 'This client is not in a transferable status or you do not have access.';
            }
        },
    }
</script>
