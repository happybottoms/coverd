<template>
    <section class="content">
        <div class="pull-right">
            <button
                class="btn btn-success btn-flat"
                @click.prevent="save"
            >
                <i class="fa fa-save fa-fw" />
                Save User
            </button>
            <div class="btn-group">
                <button
                    type="button"
                    class="btn btn-default dropdown-toggle dropdown btn-flat"
                    data-toggle="dropdown"
                >
                    <span class="fa fa-ellipsis-v" />
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a
                            href="#"
                            @click.prevent="askDelete"
                        > <i class="fa fa-trash fa-fw" />Delete User</a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit User
        </h3>

        <div class="row">
            <form role="form">
                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="icon fa fa-group fa-fw" />User Info
                            </h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- text input -->
                            <div class="form-group">
                                <label>First Name</label>
                                <input
                                    v-model="user.name.firstName"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter first name"
                                >

                                <label>Last Name</label>
                                <input
                                    v-model="user.name.lastName"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter last name"
                                >

                                <label>Email Address</label>
                                <input
                                    v-model="user.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="Enter email address"
                                >

                                <label>Password</label>
                                <input
                                    v-model="user.plainTextPassword"
                                    type="password"
                                    class="form-control"
                                    placeholder="Enter new password"
                                >
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="icon fa fa-lock fa-fw" />Groups
                            </h3>
                            <div class="box-body">
                                <div
                                    v-for="sysGroup in sysGroups"
                                    :key="sysGroup.id"
                                >
                                    <input
                                        :id="'group-' + sysGroup.id"
                                        v-model="user.groups"
                                        type="checkbox"
                                        name="group[]"
                                        :value="sysGroup"
                                    >
                                    <label :for="'group-' + sysGroup.id">{{ sysGroup.name }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="icon fa fa-shopping-cart fa-fw" />Partner
                            </h3>
                            <PartnerSelectionForm
                                v-model="selectedPartner"
                                label="Partner Assignments"
                                :options="allPartners"
                            />
                            <button
                                class="btn btn-success btn-flat"
                                :disabled="!selectedPartner.id"
                                @click="onPartnerAddClick"
                            >
                                Add Partner<i class="fa fa-fw fa-plus-circle" />
                            </button>
                            <table class="table table-hover">
                                <tr
                                    v-for="partner in user.partners"
                                    :key="partner.id"
                                >
                                    <td v-text="partner.title" />
                                    <td>
                                        <button
                                            class="btn btn-success btn-flat"
                                            @click="onPartnerRemoveClick(partner.id)"
                                        >
                                            <i class="fa fa-fw fa-trash" />
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <modal
            id="confirmModal"
            :confirm-action="this.deleteUser"
            classes="modal-danger"
        >
            <template slot="header">
                Delete User
            </template>
            <p>
                Are you sure you want to delete <strong>{{ user.name.firstName }} {{ user.name.lastName }}</strong>?
            </p>
            <template slot="confirmButton">
                Delete User
            </template>
        </modal>
    </section>
</template>

<script>
import Modal from "../../components/Modal.vue";
import { mapGetters } from "vuex";
import PartnerSelectionForm from "../../components/PartnerSelectionForm";

export default {
    components: {
        PartnerSelectionForm,
        modal: Modal
    },
    props: ["new"],
    data() {
        return {
            user: {
                name: {},
                groups: [],
                partners: []
            },
            sysGroups: {},
            sysPartners: {},
            selectedPartner: { id: 0 }
        };
    },
    computed: {
        ...mapGetters(["allActivePartners", "allPartners"])
    },
    created() {
        let self = this;

        if (!this.new) {
            axios
                .get("/api/users/" + this.$route.params.id, { params: { include: ["groups", "partners"] } })
                .then(response => {
                    self.user = response.data.data;
                    console.log("user", response.data.data);
                })
                .catch(error => console.log("Error receiving users %o", error));
        }

        axios
            .get("/api/groups")
            .then(response => {
                self.sysGroups = response.data.data;
            })
            .catch(error => console.log("Error receiving groups %o", error));

        this.$store.dispatch("loadStorageLocations");

        console.log("UserEdit Component mounted.");
    },
    methods: {
        save: function() {
            let self = this;
            if (this.new) {
                axios
                    .post("/api/users", this.user)
                    .then(response => self.$router.push({ name: "admin-users" }))
                    .catch(function(error) {
                        console.log("Save this.user error %o", error);
                    });
            } else {
                axios
                    .patch("/api/users/" + this.$route.params.id, this.user)
                    .then(response => self.$router.push({ name: "admin-users" }))
                    .catch(function(error) {
                        console.log("Save this.user error with params id %o", error);
                    });
            }
        },
        askDelete: function() {
            $("#confirmModal").modal("show");
        },
        deleteUser: function() {
            let self = this;
            axios.delete("/api/users/" + this.$route.params.id).then(self.$router.push({ name: "admin-users" }));
        },
        onPartnerAddClick: function() {
            this.user.partners.push(this.$store.getters.getStorageLocationById(this.selectedPartner.id));
            // this.user.partners = values;
        },
        onPartnerRemoveClick: function(partnerId) {
            this.user.partners = this.user.partners.filter(partner => partner.id !== partnerId);
        }
    }
};
</script>
