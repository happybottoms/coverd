<template>
    <section class="content">
        {{ isDisabledCreate }}
        <router-link
            :disabled="isDisabledCreate"
            :event="isDisabledCreate ? '' : 'click'"
            :to="{ name: 'client-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />
            Create Client
        </router-link>
        <h3 class="box-title">
            Client List
        </h3>

        <div class="row">
            <div class="col-xs-2">
                <TextField
                    v-model="filters.keyword"
                    label="Keyword"
                />
            </div>
            <div class="col-xs-3">
                <PartnerSelectionForm
                    v-model="filters.partner"
                    label="Assigned Partner"
                    :options="allPartners"
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
                        <div class="col-xs-12">
                            <div class="input-group-btn">
                                <button
                                    type="button"
                                    class="btn btn-info btn-flat dropdown-toggle"
                                    data-toggle="dropdown"
                                    :disabled="selection.length < 2"
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
                                    <li>
                                        <a @click="onClickMerge()">
                                            <i class="fa fa-compress fa-fw" /> Merge Clients
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <TablePaged
                            ref="hbtable"
                            :columns="columns"
                            api-url="/api/clients"
                            edit-route="/clients/"
                            :sort-order="[{ field: 'id', direction: 'desc' }]"
                            :params="requestParams()"
                            :per-page="50"
                            link-display-property="id"
                        >
                            <template v-slot:actions="{ rowData }">
                                <button
                                    v-if="rowData.canReview"
                                    class="btn btn-xs btn-primary"
                                    @click="onMarkReviewClicked(rowData.id)"
                                >
                                    <i class="fa fa-check-square fa-fw" />
                                    Mark Reviewed
                                </button>
                            </template>
                        </TablePaged>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
        <ClientMerge
            ref="clientMerge"
            :selected-client-ids="selection"
        />
    </section>
</template>

<script>
import ClientMerge from "./ClientMerge";
import PartnerSelectionForm from "../../components/PartnerSelectionForm";
import TablePaged from "../../components/TablePaged";
import TextField from "../../components/TextField";
import { mapGetters } from "vuex";

export default {
    components: {
        TextField,
        ClientMerge,
        PartnerSelectionForm,
        TablePaged
    },
    props: [],
    data() {
        return {
            columns: [
                { name: "__checkbox", title: "#" },
                { name: "__slot:link", title: "ID", sortField: "c.id" },
                { name: "fullName", title: "Name", sortField: "c.fullName" },
                { name: "partner.title", title: "Assigned Partner", sortField: "partner.title" },
                { name: "status", title: "Status", callback: "statusFormat", sortField: "status" },
                { name: "createdAt", title: "Created", callback: "dateTimeFormat", sortField: "createdAt" },
                { name: "updatedAt", title: "Last Updated", callback: "dateTimeFormat", sortField: "updatedAt" },
                { name: "__slot:actions" }
            ],
            clients: {},
            statuses: [{ id: "ACTIVE", name: "Active" }, { id: "INACTIVE", name: "Inactive" }],
            filters: {
                keyword: null,
                partner: { id: null }
            },
            selection: []
        };
    },
    computed: {
        ...mapGetters(["allPartners", "userActivePartner", "isAdmin", "isPartner"]),
        isDisabledCreate: function() {
            if (this.isAdmin || !this.isPartner) return false
            if (!this.userActivePartner) return true
            return this.userActivePartner.status !== 'ACTIVE' && this.userActivePartner.status !== 'NEEDS_PROFILE_REVIEW'
        }
    },
    created() {
        axios.get("/api/clients").then(response => (this.clients = response.data));
        console.log("Component mounted.");
    },
    mounted() {
        this.$events.$on("selection-change", eventData => this.onSelectionChange(eventData));
    },
    methods: {
        routerLink: function(id) {
            return (
                "<router-link :to=" +
                { name: "client-edit", params: { id: id } } +
                '><i class="fa fa-edit"></i> ' +
                id +
                "</router-link>"
            );
        },
        onPaginationData(paginationData) {
            this.$refs.pagination.setPaginationData(paginationData);
        },
        onChangePage(page) {
            this.$refs.vuetable.changePage(page);
        },
        doFilter() {
            console.log("doFilter:", this.requestParams());
            this.$events.fire("filter-set", this.requestParams());
        },
        onSelectionChange(selection) {
            this.selection = selection;
        },
        bulkStatusChange(statusId) {
            $("#bulkChangeModal").modal("show");
            this.bulkChange = {
                status: statusId
            };
        },
        refreshTable() {
            this.$refs.hbtable.refresh();
        },
        doBulkChange() {
            let self = this;
            axios
                .patch("/api/clients/bulk-change", {
                    ids: self.selection,
                    changes: self.bulkChange
                })
                .then(response => self.refreshTable())
                .catch(function(error) {
                    console.log(error);
                });
        },
        requestParams: function() {
            return {
                status: this.filters.status || null,
                keyword: this.filters.keyword || null,
                partner: this.filters.partner.id || null,
                include: ["partner"]
            };
        },
        onClickMerge: function() {
            this.$refs.clientMerge.reset();
            $("#clientMergeModal").modal("show");
        },
        onMarkReviewClicked: function(clientId) {
            axios
                .post("/api/clients/" + clientId + "/review")
                .then(() => {
                    this.refreshTable();
                })
                .catch(function(error) {
                    console.log("Save this.client error with params id %o", error);
                });
        }
    }
};
</script>
