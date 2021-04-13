<template>
    <section class="content">
        <div class="pull-right">
            <div class="btn-group">
                <workflow-button
                    entity-api="/api/partners"
                    :status="partner.status"
                    :workflow="partner.workflow"
                />
            </div>
            <button
                class="btn btn-success btn-flat"
                @click.prevent="save"
            >
                <i class="fa fa-save fa-fw" />Save Partner
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
                            <i class="fa fa-trash fa-fw" />Delete Partner
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Partner
        </h3>

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a
                        href="#location_tab"
                        data-toggle="tab"
                        aria-expanded="true"
                    >Location and Contacts</a>
                </li>
                <li>
                    <a
                        href="#profile_tab"
                        data-toggle="tab"
                    >Profile</a>
                </li>
                <li>
                    <a
                        href="#user_tab"
                        data-toggle="tab"
                    >Users</a>
                </li>
            </ul>
            <div class="tab-content">
                <PartnerLocationEditTab
                    id="location_tab"
                    v-model="partner"
                    class="tab-pane active"
                />
                <AttributesEditForm
                    id="profile_tab"
                    v-model="partner.profile.attributes"
                    class="tab-pane"
                />
                <PartnerUserListTab
                    id="user_tab"
                    :partner="partner"
                    class="tab-pane"
                />
            </div>
        </div>
        <modal
            id="confirmModal"
            :confirm-action="deletePartner"
            classes="modal-danger"
        >
            <template slot="header">
                Delete Partner
            </template>
            <p>Are you sure you want to delete <strong>{{ partner.title }}</strong>?</p>
            <template slot="confirmButton">
                Delete Partner
            </template>
        </modal>
    </section>
</template>


<script>
import Modal from '../../components/Modal.vue';

import PartnerLocationEditTab from './PartnerLocationEditTab';
import AttributesEditForm from "../../components/AttributesEditForm";
import PartnerUserListTab from "./PartnerUserListTab";
import WorkflowButton from "../../components/WorkflowButton";

export default {
        components: {
            WorkflowButton,
            PartnerUserListTab,
            AttributesEditForm,
            PartnerLocationEditTab,
            'modal' : Modal
        },
        props: {
            new: {
                type: String,
                default: '',
                required: false
            },
            hasTitle: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                partner: {
                    address: {},
                    contacts: [],
                    fulfillmentPeriod: {},
                    distributionMethod: {},
                    profile: {},
                    users: [],
                    status: '',
                    workflow: {},
                },
                transition: '',
            };
        },
        created() {
            var self = this;
            if (this.new) {
                this.partner.contacts.push({ isDeleted: false });
            } else {
                axios
                    .get('/api/partners/' + this.$route.params.id, {
                        params: { include: ['profile.attributes.options']}
                    }).then(response => {
                        self.partner = response.data.data;
                        self.partner.workflow = response.data.meta;
                    });
            }
        },
        methods: {
            save: function () {
                let self = this;
                if (this.new) {
                    axios
                        .post('/api/partners', this.partner)
                        .then(response => self.$router.push('/partners'))
                        .catch(function (error) {
                            console.log(error);
                        });
                } else {
                    axios
                        .patch('/api/partners/' + this.$route.params.id, this.partner)
                        .then(response => self.$router.push('/partners'))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            askDelete: function() {
                $('#confirmModal').modal('show');
            },
            deletePartner: function() {
                let self = this;
                axios
                    .delete('/api/partners/' + this.$route.params.id)
                    .then(self.$router.push('/partners'));
            },
        }
    }
</script>
