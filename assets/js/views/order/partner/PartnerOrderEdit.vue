<template>
    <section class="content">
        <div
            v-if="!partnerCanOrder"
            class="callout callout-danger"
        >
            <h4><i class="icon fa fa-ban"></i> Alert!</h4>
            The selected partner has already created a distribution for {{ order.distributionPeriod|dateTimeMonthFormat }}
        </div>
        <div class="pull-right">
            <div class="btn-group">
                <workflow-button
                    entity-api="/api/orders/partner"
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
                            <i class="fa fa-trash fa-fw" />Delete Order
                        </a>
                    </li>
                    <li>
                        <router-link :to="'/orders/partner/' + order.id + '/fill-sheet'">
                            <i class="fa fa-print fa-fw" />Print Fill Sheet
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Partner Order
        </h3>

        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <ordermetadatabox
                        :order="order"
                        order-type="Partner Order"
                        :editable="order.isEditable"
                        :v="$v.order"
                    />
                </div>

                <div class="col-md-8">
                    <div class="box box-info">
                        <div class="box-body">
                            <div
                                class="col-md-6"
                                :class="{ 'has-error': $v.order.partner.$error }"
                            >
                                <h3 class="box-title">
                                    <i class="icon fa fa-sitemap fa-fw" />Partner
                                </h3>
                                <partnerselectionform
                                    ref="partnerSelection"
                                    v-model="order.partner"
                                    :label="false"
                                    :editable="order.isEditable"
                                    @change="$v.order.partner.$touch()"
                                    @loaded="$v.order.partner.$reset()"
                                />
                                <fielderror v-if="$v.order.partner.$error">
                                    Field is required
                                </fielderror>
                            </div>

                            <div
                                class="col-md-6"
                                :class="{ 'has-error': $v.order.warehouse.$error }"
                            >
                                <h3 class="box-title">
                                    <i class="icon fa fa-industry fa-fw" />Source Warehouse
                                </h3>
                                <warehouseselectionform
                                    v-model="order.warehouse"
                                    :editable="order.isEditable"
                                    @change="$v.order.warehouse.$touch()"
                                />
                                <fielderror v-if="$v.order.warehouse.$error">
                                    Field is required
                                </fielderror>
                            </div>
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
                                <a data-toggle="collapse" href="#orderByClient" aria-expanded="true" aria-controls="orderByClient">
                                    <i class="icon fa fa-child fa-fw" />Order by Client
                                </a>
                            </h3>
                        </div>
                        <div id="orderByClient" class="panel-collapse collapse in" aria-expanded="true">
                            <LineItemByClientForm
                                :products="allOrderableProducts"
                                :line-items="order.lineItems.filter((item) => item.client)"
                                :editable="order.isEditable"
                                :show-packs="true"
                                :filter-text="filterText"
                            />
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
                                <i class="icon fa fa-boxes fa-fw" />Order in Bulk or On-hand
                            </h3>
                        </div>
                        <lineitemform
                            :products="allOrderableProducts"
                            :line-items="order.lineItems"
                            :editable="order.isEditable"
                            :show-packs="true"
                            :partner-type="partnerType"
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
        <Modal classes="modal-danger" title="Partner not active" id="inactive-partner-modal" :has-action="false">
            This partner is not in an active status and cannot place an order at this time.
        </Modal>
    </section>
</template>


<script>
import {required} from 'vuelidate/lib/validators';
import {linesRequired} from '../../../validators';
import ModalOrderConfirmComplete from '../../../components/ModalOrderConfirmComplete';
import ModalOrderConfirmDelete from '../../../components/ModalOrderConfirmDelete';
import ModalOrderInvalid from '../../../components/ModalOrderInvalid';
import FieldError from '../../../components/FieldError';
import OrderMetadataBox from '../../../components/OrderMetadataBox.vue';
import WarehouseSelectionForm from '../../../components/WarehouseSelectionForm.vue'
import LineItemForm from '../../../components/order/LineItemByProductForm.vue';
import PartnerSelectionForm from '../../../components/PartnerSelectionForm.vue';
import Modal from "../../../components/Modal";
import axios from "axios";
import LineItemByClientForm from "../../../components/order/LineItemByClientForm";
import {mapGetters} from "vuex";
import WorkflowButton from "../../../components/WorkflowButton";

export default {
        components: {
            WorkflowButton,
            LineItemByClientForm,
            Modal,
            'modalcomplete' : ModalOrderConfirmComplete,
            'modaldelete' : ModalOrderConfirmDelete,
            'modalinvalid' : ModalOrderInvalid,
            'fielderror' : FieldError,
            'ordermetadatabox' : OrderMetadataBox,
            'warehouseselectionform' : WarehouseSelectionForm,
            'lineitemform' : LineItemForm,
            'partnerselectionform' : PartnerSelectionForm
        },
        props: {
            new: { type: Boolean, default: false}
        },
        data() {
            return {
                order: {
                    lineItems: [],
                    partner: { id: null },
                    warehouse: { id: null },
                    isEditable: true,
                    isDeletable: false,
                    orderPeriod: "",
                    status: '',
                    workflow: {},
                },
                products: [],
                partnerType: 'AGENCY',
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
                warehouse: {
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
            statusIsCompleted: function () {
                var self = this;
                var status = this.statuses.filter(function(item) {
                    return self.order.status === item.id
                });
                return status[0].commit === true;
            },
            ...mapGetters([
                'allOrderableProducts'
            ])
        },
        watch: {
            'order.partner': {
                handler(val) {
                    if (this.new && this.order.partner.id && this.order.orderPeriod) {
                        axios
                            .get('/api/orders/partner/partner-can-order', {
                                params: {
                                    partnerId: this.order.partner.id,
                                    orderPeriod: this.order.orderPeriod
                                }
                            })
                            .then(response => {
                                this.partnerCanOrder = response.data.success;
                            });
                    }
                },
                deep: true
            },
            'order.orderPeriod': {
                handler(val) {
                    if (this.new && this.order.partner.id && this.order.orderPeriod) {
                        axios
                            .get('/api/orders/partner/partner-can-order', {
                                params: {
                                    partnerId: this.order.partner.id,
                                    orderPeriod: this.order.orderPeriod
                                }
                            })
                            .then(response => {
                                this.partnerCanOrder = response.data.success;
                            });
                    }
                },
            }
        },
        created() {
            var self = this;
            if (this.new) {
            } else {
                axios
                    .get('/api/orders/partner/' + this.$route.params.id, {
                        params: { include: ['lineItems', 'lineItems.product', 'lineItems.transactions', 'partner.addresses', 'partnerAddress']}
                    })
                    .then(response => {
                        self.order = response.data.data;
                        self.order.workflow = response.data.meta;
                    });
            }
        },
        mounted() {
            this.$refs.partnerSelection.$on('partner-change', eventData => this.onPartnerChange(eventData));
            this.$store.dispatch('loadProducts');
            console.log('Component mounted.')
        },
        methods: {
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
                        .post('/api/orders/partner', this.order)
                        .then(response => self.$router.push('/orders/partner'))
                        .catch(function (error) {
                            console.log(error);
                        });
                } else {
                    axios
                        .patch('/api/orders/partner/' + this.$route.params.id, this.order)
                        .then(response => self.$router.push('/orders/partner'))
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
                    .delete('/api/orders/partner/' + this.$route.params.id)
                    .then(self.$router.push('/orders/partner'));
            },
            onPartnerChange: function(partner) {
                let self = this;
                this.$v.order.partner.$touch();

                this.partnerType = partner.partnerType;
                if(!partner.canPlaceOrders && this.new) {
                    $('#inactive-partner-modal').modal('show');
                }

                axios
                    .get('/api/orders/partner/new-line-items-for-partner/' + partner.id, {
                        params: { include: ['client.lastDistribution']}
                    })
                    .then((response) => {
                            self.order.lineItems = response.data.data;
                            // resolve(response);
                        },
                        (err) => {
                            // reject(err);
                        }
                    );
            },
        }
    }
</script>
