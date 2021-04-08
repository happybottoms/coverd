<template>
    <section class="content">
        <div
            v-if="!partnerCanOrder"
            class="alert alert-danger alert-dismissible"
        >
            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
            The selected partner has already created a distribution for {{ order.distributionPeriod|dateTimeMonthFormat }}
        </div>
        <div class="pull-right">
            <div class="btn-group">
                <workflow-button
                    entity-api="/api/orders/distribution"
                    :status="order.status"
                    :workflow="order.workflow"
                />
                <button
                    class="btn btn-success btn-flat"
                    :disabled="!order.isEditable"
                    @click.prevent="saveVerify"
                >
                    <i class="fa fa-save fa-fw" />Save Order
                </button>
            </div>
            <div class="btn-group">
                <button
                    type="button"
                    class="btn btn-default dropdown-toggle dropdown btn-flat"
                    data-toggle="dropdown"
                >
                    <span class="fa fa-ellipsis-v" />
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li v-if="order.isDeletable">
                        <a
                            href="#"
                            @click.prevent="askDelete"
                        >
                            <i class="fa fa-trash fa-fw" />Delete Distribution
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Partner Distribution
        </h3>

        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <ordermetadatabox
                        :order="order"
                        :editable="order.isEditable"
                        order-type="Partner Distribution Order"
                        :v="$v.order"
                    />
                </div>

                <div class="col-md-8">
                    <div class="box box-info">
                        <div
                            class="box-body"
                            :class="{ 'has-error': $v.order.partner.$error }"
                        >
                            <h3 class="box-title">
                                <i class="icon fa fa-sitemap fa-fw" />Partner
                            </h3>
                            <partnerselectionform
                                v-model="order.partner"
                                :editable="order.isEditable"
                                @partner-change="onPartnerChange"
                                @loaded="$v.order.partner.$reset()"
                            />
                            <fielderror v-if="$v.order.partner.$error">
                                Field is required
                            </fielderror>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div
                        class="box box-info"
                        :class="{ 'has-error': $v.order.lineItems.$error }"
                    >
                        <div class="box-header with-border">
                            <fielderror
                                v-if="$v.order.lineItems.$error"
                                classes="pull-right"
                            >
                                At least one line item must have a quantity
                            </fielderror>
                            <h3 class="box-title">
                                <i class="icon fa fa-list fa-fw" />Line Items
                            </h3>
                            <div class="box-tools pull-right">
                                <TextField
                                    v-model="filterText"
                                    placeholder="Filter"
                                />
                            </div>
                        </div>
                        <LineItemByClientForm
                            :products="allOrderableProducts"
                            :line-items="order.lineItems"
                            :editable="order.isEditable"
                            :show-packs="true"
                            :filter-text="filterText"
                        />
                    </div>
                </div>
            </div>
        </form>
        <modalinvalid />
        <modaldelete
            :action="this.deleteOrder"
            :order-title="order.sequence"
        />
        <modalcomplete
            :action="this.save"
            :order-title="order.sequence"
        />
    </section>
</template>


<script>
import {required} from 'vuelidate/lib/validators';
import {linesRequired} from '../../../validators';
import {mapGetters} from 'vuex';
import ModalOrderConfirmComplete from '../../../components/ModalOrderConfirmComplete.vue';
import ModalOrderConfirmDelete from '../../../components/ModalOrderConfirmDelete.vue';
import ModalOrderInvalid from '../../../components/ModalOrderInvalid.vue';
import FieldError from '../../../components/FieldError.vue';
import OrderMetadataBox from '../../../components/OrderMetadataBox.vue';
import PartnerSelectionForm from '../../../components/PartnerSelectionForm.vue';
import axios from "axios";
import TextField from "../../../components/TextField";
import LineItemByClientForm from "../../../components/order/LineItemByClientForm";
import WorkflowButton from "../../../components/WorkflowButton";

export default {
        components: {
            WorkflowButton,
            LineItemByClientForm,
            TextField,
            'modalcomplete' : ModalOrderConfirmComplete,
            'modaldelete' : ModalOrderConfirmDelete,
            'modalinvalid' : ModalOrderInvalid,
            'fielderror' : FieldError,
            'ordermetadatabox' : OrderMetadataBox,
            'partnerselectionform' : PartnerSelectionForm,
        },
        props: {
            new: { type: Boolean, default: false}
        },
        data() {
            return {
                order: {
                    lineItems: [],
                    partner: { id: null },
                    isEditable: true,
                    isDeletable: false,
                    distributionPeriod: '',
                    reason: '',
                    status: '',
                    workflow: {},
                },
                products: [],
                filterText: "",
                partnerCanOrder: true
            };
        },
        validations: {
            order: {
                partner: {
                    id: {
                        required
                    }
                },
                status: {
                    required
                },
                lineItems: { linesRequired }
            }
        },
        computed: {
            ...mapGetters([
                'allOrderableProducts',
            ]),
            statusIsCompleted: function () {
                var self = this;
                var status = this.statuses.filter(function(item) {
                    return self.order.status === item.id
                });
                return status[0].commit === true;
            },

        },
        watch: {
            'order.partner': {
                handler(val) {
                    if (this.new && this.order.partner.id && this.order.distributionPeriod) {
                        axios
                            .get('/api/orders/distribution/partner-can-order', {
                                params: {
                                    partnerId: this.order.partner.id,
                                    distributionPeriod: this.order.distributionPeriod
                                }
                            })
                            .then(response => {
                                this.partnerCanOrder = response.data.success;
                            });
                    }
                },
                deep: true
            },
            'order.distributionPeriod': {
                handler(val) {
                    if (this.new && this.order.partner.id && this.order.distributionPeriod) {
                        axios
                            .get('/api/orders/distribution/partner-can-order', {
                                params: {
                                    partnerId: this.order.partner.id,
                                    distributionPeriod: this.order.distributionPeriod
                                }
                            })
                            .then(response => {
                                this.partnerCanOrder = response.data.success;
                            });
                    }
                },
            }
        },
        mounted() {
            var self = this;
            this.$store.dispatch('loadProducts');

            if (this.new) {
            } else {
                axios
                    .get('/api/orders/distribution/' + this.$route.params.id, {
                        params: { include: ['lineItems', 'lineItems.product', 'lineItems.transactions', 'partner.addresses']}
                    })
                    .then(response => {
                        self.order = response.data.data;
                        self.order.workflow = response.data.meta;
                    });
            }

            console.log('Component mounted.')
        },
        methods: {
            onPartnerChange: function(partner) {
                let self = this;
                this.$v.order.partner.$touch();
                axios
                    .get('/api/orders/distribution/new-line-items-for-partner/' + partner.id)
                    .then((response) => {
                            self.order.lineItems = response.data.data;
                            // resolve(response);
                        },
                        (err) => {
                            // reject(err);
                        }
                    );
            },
            saveVerify: function () {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    $('#invalidModal').modal('show');
                    return false;
                }
                if (this.statusIsCompleted) {
                    $('#confirmCommitModal').modal('show');
                } else {
                    this.save();
                }
            },
            save: function () {
                var self = this;
                if (this.new) {
                    axios
                        .post('/api/orders/distribution', this.order)
                        .then(response => self.$router.push('/orders/distribution'))
                        .catch(function (error) {
                            console.log(error);
                        });
                } else {
                    axios
                        .patch('/api/orders/distribution/' + this.$route.params.id, this.order)
                        .then(response => self.$router.push('/orders/distribution'))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            askDelete: function() {
                $('#confirmModal').modal('show');
            },
            deleteOrder: function() {
                var self = this;
                axios
                    .delete('/api/orders/distribution/' + this.$route.params.id)
                    .then(self.$router.push('/orders/distribution'));
            },
            checkPartnerCanOrder: (val) => {
                if (this.order.partner.id && this.order.distributionPeriod) {
                    axios
                        .get('/api/orders/distribution/partner-can-order', {
                            params: {
                                partnerId: this.order.partner.id,
                                distributionPeriod: this.order.distributionPeriod
                            }
                        })
                        .then(response => {
                            this.partnerCanOrder = !response.data.success;
                        });
                }
            }
        }
    }
</script>
