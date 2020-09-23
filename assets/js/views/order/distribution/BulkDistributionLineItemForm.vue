<template>
    <div>
        <div class="box-body form-horizontal">
            <div class="form-group">
                <h4 class="col-xs-8">
                    Client
                </h4>
                <h4 class="col-xs-2">
                    Product
                </h4>
                <h4 class="col-xs-2">
                    Quantity
                </h4>
            </div>
            <lineitemformrow
                v-for="(lineItem, index) in lineItems"
                v-show="!filterText || filterResults.includes(lineItem.client.id)"
                :key="lineItem.client.id"
                v-model="lineItems[index]"
                :editable="editable"
                :partner-type="partnerType"
            />
        </div>
    </div>
</template>

<script>
import BulkDistributionLineItemFormRow from './BulkDistributionLineItemFormRow';

export default {
        components: {
            'lineitemformrow' : BulkDistributionLineItemFormRow
        },
        props: {
            products: { type: Array, required: true },
            lineItems: { type: Array, required: true },
            editable: { type: Boolean, default: true },
            partnerType: { type: String, default: 'AGENCY' },
            filterText: { type: String, default: '' }
        },
        computed: {
            filterResults: function() {
                let self = this;
                return this.lineItems
                    .filter(function(item) {
                        let testString = item.client.fullName.replace(/\s/g, '');
                        return testString.toLowerCase().includes(self.filterText.replace(/\s/g, '').toLowerCase());
                    })
                    .map((item) => item.client.id);
            }
        },
        methods: {
            findLineItem: function(product) {
                let self = this;
                let lineItem = this.lineItems.filter(function(line) {
                    return line.product.id === product.id;
                }).pop();

                if (!lineItem) {
                    lineItem = self.lineItems.push({
                        product: product,
                        cost: product.defaultCost,
                        quantity: null,
                    });
                }

                return lineItem;
            }
        }
    }
</script>
