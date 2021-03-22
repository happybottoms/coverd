<template>
    <section class="content">
        <div class="pull-right">
            <div class="btn-group">
                <button
                    class="btn btn-info btn-flat dropdown-toggle"
                    data-toggle="dropdown"
                >
                    <i class="fa fa-project-diagram fa-fw" /> Status: {{ client.status | statusFormat }} <i class="fa fa-caret-down fa-fw"></i>
                </button>
                <ul
                    v-if="client.workflow.enabledTransitions"
                    class="dropdown-menu dropdown-menu-right"
                >
                    <li
                        v-for="enabledTransition in getSortedTransitions()"
                        :key="enabledTransition.transition"
                    >
                        <a
                            @click.prevent="doTransition(enabledTransition.transition)"
                        >
                            <i class="fa fa-arrow-circle-right" />{{ enabledTransition.title }}
                        </a>
                    </li>
                </ul>
            </div>
            <button
                v-if="client.canReview"
                class="btn btn-primary btn-flat"
                @click.prevent="review"
            >
                <i class="fa fa-check-square fa-fw" />
                Mark Client Reviewed
            </button>
            <button
                class="btn btn-success btn-flat"
                @click.prevent="save"
            >
                <i class="fa fa-save fa-fw" />
                Save Client
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
                        >
                            <i class="fa fa-trash fa-fw" />Delete Client</a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Client
        </h3>

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
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a
                                            href="#client_info"
                                            data-toggle="tab"
                                            aria-expanded="true"
                                        >Client Information</a>
                                    </li>
                                    <li>
                                        <a
                                            href="#profile_tab"
                                            data-toggle="tab"
                                        >Client Profile</a>
                                    </li>
                                    <li>
                                        <a
                                            href="#distribution_history"
                                            data-toggle="tab"
                                        >Distribution History</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <ClientInfoForm
                                        v-model="client"
                                    />
                                    <AttributesEditForm
                                        id="profile_tab"
                                        v-model="client.attributes"
                                        class="tab-pane"
                                    />
                                    <ClientDistributionHistory
                                        id="distribution_history"
                                        :client="client"
                                        class="tab-pane"
                                    />
                                </div>
                            </div>
                            <!-- text input -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </form>
        </div>

        <modal
            id="confirmModal"
            :confirm-action="deleteClient"
            classes="modal-danger"
        >
            <template slot="header">
                Delete Client
            </template>
            <p>Are you sure you want to delete <strong>{{ client.firstName }} {{ client.lastName }}</strong>?</p>
            <template slot="confirmButton">
                Delete Client
            </template>
        </modal>
    </section>
</template>

<script>
import Modal from '../../components/Modal.vue';
import AttributesEditForm from "../../components/AttributesEditForm";
import ClientDistributionHistory from "./ClientDistributionHistory";
import ClientInfoForm from "./ClientInfoForm";

export default {
        name: 'ClientEdit',
        components: {
            ClientInfoForm,
            ClientDistributionHistory,
            AttributesEditForm,
            'modal' : Modal
        },
        props: {
            new: {
                type: Boolean,
                default: false,
                required: false
            }

        },
        data() {
            return {
                client: {
                    partner: {},
                    attributes: [],
                    status: '',
                    workflow: {},
                },
                transition: '',
            };
        },
        created() {
            let self = this;

            if (!this.new) {
                axios
                    .get('/api/clients/' + this.$route.params.id, {
                        params: { include: ['partner', 'attributes', 'attributes.options']}
                    })
                    .then(response => {
                        self.client = response.data.data;
                        self.client.workflow = response.data.meta;
                    })
                    .catch(error => console.log("Error receiving clients %o", error));
            }

            console.log('ClientEdit Component mounted.');
        },
        methods: {
            save: function () {
                let self = this;
                if (this.new) {
                    axios
                        .post('/api/clients', this.client)
                        .then(response => self.$router.push({ name: 'clients' }))
                        .catch(function (error) {
                            console.log("Save this.client error %o", error);
                        });
                } else {
                    axios
                        .patch('/api/clients/' + this.$route.params.id, this.client)
                        .then(response => self.$router.push({ name: 'clients' }))
                        .catch(function (error) {
                            console.log("Save this.client error with params id %o", error);
                        });
                }
            },
            doTransition: function(transition) {
                let self = this;
                axios.patch('/api/clients/' + this.$route.params.id + '/transition', {'transition': transition})
                    .then(response => {
                        self.client.workflow = response.data.meta;
                        self.client.status = response.data.data.status;
                    });
            },
            askDelete: function() {
                $('#confirmModal').modal('show');
            },
            deleteClient: function() {
                let self = this;
                axios
                    .delete('/api/clients/' + this.$route.params.id)
                    .then(response => self.$router.push({ name: 'clients' }));
            },
            review: function() {
                let self = this;
                axios
                    .post('/api/clients/' + this.$route.params.id + '/review', {
                        params: { include: ['partner', 'attributes']}
                    })
                    .then(response => {
                        self.client = response.data.data;
                        self.client.workflow = response.data.meta;
                    })
                    .catch(function (error) {
                        console.log("Save this.client error with params id %o", error);
                    }
                );
            },
            getSortedTransitions() {
                const inputObject = this.client.workflow.enabledTransitions;

                return Object
                    .keys(inputObject)
                    .map((key) => inputObject[key])
                    .sort(function (a, b) {
                        let titleA = a.title.toUpperCase();
                        let titleB = b.title.toUpperCase();

                        if (titleA < titleB) {
                            return -1;
                        }

                        if (titleA > titleB) {
                            return 1;
                        }

                        return 0;
                    }
                );
            }
        }
    }
</script>

<style scoped>

</style>
