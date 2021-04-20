<template>
    <section class="content">
        <ClientTransferModal
            :client="transferClient"
            :target-partners="userPartners || null"
        />

        <div class="pull-right">
            <button
                v-if="noDuplicates"
                class="btn btn-success btn-flat"
                @click.prevent="save"
            >
                <i class="fa fa-save fa-fw" />
                Save Client
            </button>
            <button
                v-if="!checked"
                class="btn btn-primary btn-flat"
                @click="check()"
            >
                <i class="fa fa-search fa-fw" />
                Check for Duplicate
            </button>
        </div>

        <h3 class="box-title">
            New Client
        </h3>

        <section
            v-if="!checked"
            id="new-client-check"
        >
            <div class="row">
                <form role="form">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="icon fa fa-child fa-fw" />Client Info
                                </h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div
                                    id="client_info"
                                    class="row active"
                                >
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <div :class="{ 'has-error': $v.client.firstName.$error }">
                                                <input
                                                    v-model="client.firstName"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Enter first name"
                                                >
                                                <fielderror v-if="$v.client.firstName.$error">
                                                    First Name is required
                                                </fielderror>
                                            </div>
                                            <label>Last Name</label>
                                            <div :class="{ 'has-error': $v.client.lastName.$error }">
                                                <input
                                                    v-model="client.lastName"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Enter last name"
                                                >
                                                <fielderror v-if="$v.client.lastName.$error">
                                                    Last Name is required
                                                </fielderror>
                                            </div>
                                        </div>
                                        <div :class="{ 'has-error': $v.client.birthdate.$error }">
                                            <DateField
                                                v-model="client.birthdate"
                                                label="Birthdate"
                                                format="YYYY-MM-DD"
                                            />
                                            <fielderror v-if="$v.client.birthdate.$error">
                                                Birthdate should be a date in the past
                                            </fielderror>
                                        </div>
                                    </div>
                                </div>
                                <AttributesEditForm
                                    id="profile_tab"
                                    v-model="client.attributes"
                                    :filter="(attribute) => attribute.isDuplicateReference"
                                />
                                <!-- text input -->
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-md-12" />
            </div>
        </section>

        <section
            v-if="foundDuplicates"
            id="duplicates"
        >
            <div class="callout callout-danger">
                <h3>Duplicate Clients Found</h3>
                Based on the information provided, this client appears to be a duplicate of the following client(s). If
                inactive, please transfer a client below to your partner agency.
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-body">
                            <table class="table table-hover">
                                <thead>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                    <th>Partner</th>
                                    <th />
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="duplicate in duplicates"
                                        :key="duplicate.id"
                                    >
                                        <td v-text="duplicate.fullName" />
                                        <td v-text="duplicate.status" />
                                        <td>
                                            <span
                                                v-if="duplicate.lastDistribution"
                                                v-text="duplicate.lastDistribution.order.distributionPeriod"
                                            />
                                        </td>
                                        <td v-text="duplicate.partner ? duplicate.partner.title : ''" />
                                        <td>
                                            <button
                                                v-tooltip
                                                :disabled="!duplicate.canPartnerTransfer"
                                                :title="transferButtonTooltip(duplicate)"
                                                class="btn btn-xs btn-primary"
                                                @click="onTransferClicked(duplicate)"
                                            >
                                                <i class="fa fa-exchange-alt fa-fw" />
                                                Transfer to Active Partner
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="noDuplicates"
            id="new-client"
        >
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-body">
                            <ClientInfoForm
                                ref="clientInfoForm"
                                v-model="client"
                                :show-expirations="false"
                            />
                            <AttributesEditForm v-model="client.attributes" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <modalinvalid />
    </section>
</template>

<script>
import AttributesEditForm from "../../components/AttributesEditForm";
import DateField from "../../components/DateField";
import ClientInfoForm from "./ClientInfoForm";
import { mapGetters } from "vuex";
import ClientTransferModal from "./ClientTransferModal";
import { required } from "vuelidate/lib/validators";
import { mustLessThanNow } from "../../validators";
import FieldError from "../../components/FieldError";
import ModalOrderInvalid from "../../components/ModalOrderInvalid";

export default {
    name: "ClientNewCheck",
    components: {
        ClientTransferModal,
        ClientInfoForm,
        DateField,
        AttributesEditForm,
        fielderror: FieldError,
        modalinvalid: ModalOrderInvalid,
    },
    data() {
        return {
            client: {
                attributes: [],
                partner: {},
            },
            duplicates: [],
            checked: false,
            transferClient: {},
        };
    },
    validations: {
        client: {
            firstName: {
                required,
            },
            lastName: {
                required,
            },
            birthdate: {
                required,
                mustLessThanNow,
            },
        },
    },
    computed: {
        ...mapGetters(["userActivePartner", "userPartners"]),
        foundDuplicates: function () {
            return this.checked && this.duplicates.length > 0;
        },
        noDuplicates: function () {
            return this.checked && this.duplicates.length === 0;
        },
    },
    created() {
        let self = this;
        axios
            .get("/api/clients/fields", {
                params: { include: ["options"] },
            })
            .then((response) => (this.client.attributes = response.data.data))
            .catch(function (error) {
                console.log("Save this.client error %o", error);
            });
        console.log("ClientEdit Component mounted.");
    },
    methods: {
        check: function () {
            let self = this;
            this.$v.$touch();
            if (this.$v.$invalid) {
                $("#invalidModal").modal("show");
                return false;
            }
            axios
                .post("/api/clients/new-check", this.client, {
                    params: { include: ["partner", "lastDistribution", "lastDistribution.order"] },
                })
                .then((response) => {
                    self.duplicates = response.data.data;
                    self.checked = true;
                })
                .catch(function (error) {
                    console.log("Save this.client error %o", error);
                });
        },
        save: function () {
            let self = this;
            if (this.$refs.clientInfoForm) {
                this.$refs.clientInfoForm.$v.$touch();
                if (this.$refs.clientInfoForm.$v.$invalid) {
                    $("#invalidModal").modal("show");
                    return false;
                }
            }
            axios
                .post("/api/clients", this.client)
                .then((response) => self.$router.push({ name: "clients" }))
                .catch(function (error) {
                    console.log("Save this.client error %o", error);
                });
        },
        onTransferClicked: function (client) {
            this.transferClient = client;
            $("#clientTransferModal").modal("show");
        },
        transferButtonTooltip: function (rowData) {
            if (rowData.canPartnerTransfer) {
                return "Transfer client to " + this.userActivePartner.title;
            }
            return "This client is not in a transferable status or you do not have access.";
        },
    },
};
</script>
