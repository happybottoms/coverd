<template>
    <div class="box box-info">
        <div class="box-body">
            <h2>{{ order.sequence }}</h2>
            <div
                v-if="order.hasOwnProperty('orderPeriod')"
                class="form-group"
            >
                <datefield
                    v-if="editable"
                    v-model="order.orderPeriod"
                    label="Order Period:"
                    format="YYYY-MM"
                    timezone="Etc/UTC"
                />

                <template v-else>
                    <strong>Order Period:</strong>
                    <span>{{ order.orderPeriod | dateTimeMonthFormat }}</span>
                </template>
            </div>

            <div
                v-if="order.hasOwnProperty('distributionPeriod')"
                class="form-group"
            >
                <template v-if="editable">
                    <datefield
                        v-model="order.distributionPeriod"
                        format="YYYY-MM"
                        timezone="Etc/UTC"
                        label="Distribution Period:"
                    />
                </template>

                <template v-else>
                    <strong>Distribution Period:</strong>
                    <span>{{ order.distributionPeriod | dateTimeMonthFormat }}</span>
                </template>
            </div>

            <div
                v-if="order.hasOwnProperty('receivedAt')"
                class="form-group"
            >
                <template v-if="editable">
                    <datefield
                        v-model="order.receivedAt"
                        label="Received Date:"
                    />
                </template>

                <template v-else>
                    <strong>Received Date:</strong>
                    <span>{{ order.receivedAt | dateFormat }}</span>
                </template>
            </div>
            <div
                v-if="order.hasOwnProperty('portalOrderId')"
                class="form-group"
            >
                <strong>Portal Order ID:</strong>
                <span>{{ order.portalOrderId }}</span>
            </div>
            <strong>Created:</strong> {{ order.createdAt | dateTimeFormat }}<br>
            <strong>Last Updated:</strong> {{ order.updatedAt | dateTimeFormat }}
        </div>
        <!-- /.box-body -->
    </div>
</template>

<script>
import DateField from '../components/DateField.vue';

export default {
        components: {
            'datefield': DateField,
        },
        props: {
            order: { type: Object, required: true },
            orderType: { type: String, required: true, },
            editable: { type: Boolean, default: true },
            v: { type: Object },
        },
        computed: {
            statusError: function () {
                return this.v && this.v.status && this.v.status.$error;
            },
        }
    }
</script>
