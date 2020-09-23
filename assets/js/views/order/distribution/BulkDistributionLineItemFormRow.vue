<template>
    <div>
        <div>
            <label :class="{'col-xs-6': showCost || showPacks, 'col-xs-8': !showCost && !showPacks }">
                <i
                    class="fa fa-plus-circle text-gray fa-fw"
                    title="Show/Hide Transactions"
                    @click="showTransactions = !showTransactions"
                />
                {{ value.client.fullName }}
            </label>
            <div
                class="col-xs-2"
            >
                <ProductSelectionField
                    v-model="value.product"
                    :label="false"
                />
            </div>
            <div
                class="col-xs-2"
                :class="{'has-error': showPacks && value.quantity % packSize != 0}"
            >
                <OptionListStatic
                    v-if="editable && selectedProduct"
                    v-model="value.quantity"
                    :preloaded-options="quantityOptions"
                    empty-string="0"
                    :v-chosen="false"
                />
                <span
                    v-else
                    v-text="value.quantity"
                />
            </div>
            <div
                v-if="showCost"
                class="col-xs-2"
            >
                <input
                    v-if="editable"
                    v-model="value.cost"
                    type="text"
                    class="form-control"
                >
                <span
                    v-else
                    v-text="lineItem.cost"
                />
            </div>
        </div>
        <div
            v-show="showTransactions"
            class="form-group"
        >
            <div class="col-xs-12">
                <lineitemtransactions :transactions="value.transactions" />
            </div>
        </div>
    </div>
</template>

<script>
    import LineItemTransactionTable from "../../../components/LineItemTransactionTable";
    import OptionListStatic from "../../../components/OptionListStatic";
    import { mapGetters } from 'vuex'
    import ProductSelectionField from "../../../components/ProductSelectionField";

    export default {
        name: "BulkDistributionLineItemFormRow",
        components: {
            OptionListStatic,
            ProductSelectionField,
            'lineitemtransactions' : LineItemTransactionTable
        },
        props: {
            value: { type: Object, required: true },
            editable: { type: Boolean, default: true },
            showCost: { type: Boolean, default: false },
            showPacks: { type: Boolean, default: false },
            partnerType: { type: String, default: 'AGENCY' },
        },
        data() {
            return {
                showTransactions: false,
            };
        },
        computed: {
            ...mapGetters([
                'allActiveProducts',
            ]),
            packSize: function () {
                if (!this.selectedProduct) return 0;

                if (this.partnerType === 'HOSPITAL' && this.selectedProduct.hospitalPackSize) {
                    return this.selectedProduct.hospitalPackSize;
                }

                return this.selectedProduct.agencyPackSize;
            },
            maxPacks: function() {
                if (!this.selectedProduct) return 0;

                if (this.partnerType === 'HOSPITAL' && this.selectedProduct.hospitalPackSize) {
                    return this.selectedProduct.hospitalMaxPacks;
                }

                return this.selectedProduct.agencyMaxPacks;
            },
            quantityOptions: function () {
                let options = [];

                if (this.selectedProduct) {
                    for (let i=1; i <= this.maxPacks; i++) {
                        options.push({
                            name: this.packSize * i,
                            id: this.packSize * i
                        })
                    }
                }

                return options;
            },
            packs: function () {
                return this.value.quantity / this.packSize;
            },
            selectedProduct: function () {
                if (this.value.product.id == null) return null;
                return this.$store.getters.getProductById(this.value.product.id);
            }
        },
    }
</script>
