<template>
    <div class="table-responsive no-padding box" style="min-height:500px">
        <div class="overlay" v-if="loading"><i class="fa fa-sync fa-spin" /></div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th class="text-right">
                        All Stock
                        <i
                            v-tooltip="'bottom'"
                            class="fa fa-question-circle"
                            title="This level represents all stock physically located on-site"
                        />
                    </th>
                    <th class="text-right">
                        Stock (including pending orders)
                        <i
                            v-tooltip="'bottom'"
                            class="fa fa-question-circle"
                            title="This level represents stock on-site including any pending partner orders where products are allocated but not shipped"
                        />
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="product in products"
                    :key="product.id"
                >
                    <td>
                        <router-link
                            :to="'/products/' + product.id"
                        >
                            <i class="fa fa-edit" />{{ product.id }}
                        </router-link>
                    </td>
                    <td v-text="product.name" />
                    <td v-text="product.category" />
                    <td
                        class="text-right"
                        v-text="product.balance.toLocaleString()"
                    />
                    <td
                        class="text-right"
                        v-text="product.availableBalance.toLocaleString()"
                    />
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    name: 'WarehouseStock',
    data() {
        return {
            products: {},
            loading: false,
        };
    },
    created() {
        this.getLevels();
    },
    methods: {
        getLevels: function() {
            this.loading = true;
            axios
                .get('/api/stock-levels', { params: this.buildParams() })
                .then(response => {
                    this.products = response.data;                    
                    this.loading = false;
                });
        },
        buildParams: function() {
            return {
                locationType: 'WAREHOUSE',
            };
        },
    },
};
</script>
