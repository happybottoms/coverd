<template>
    <section class="content">
        <h3 class="box-title">
            Stock Levels
        </h3>

        <div class="row">
            <div class="col-xs-4">
                <storagelocationselectionform
                    v-model="filters.location"
                />
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label>Location Type</label>
                    <select
                        v-model="filters.locationType"
                        v-chosen
                        class="form-control"
                    >
                        <option value="">
                            --All Location Types--
                        </option>
                        <option value="WAREHOUSE">
                            Warehouses
                        </option>
                        <option value="PARTNER">
                            Partners
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group col-xs-4">
                <datefield
                    v-model="filters.endingAt"
                    label="Date"
                />
            </div>
            <div class="col-xs-1 text-right">
                <button
                    class="btn btn-success btn-flat"
                    @click="doFilter"
                >
                    <i class="fa fa-fw fa-filter" />Filter
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box" style="min-height:500px">
                    <div class="overlay" v-if="loading"><i class="fa fa-sync fa-spin" /></div>
                    <div class="box-body table-responsive no-padding">
                        <table
                            class="table table-hover"
                        >
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
                                        <router-link :to="{ name: 'products', params: { id: product.id }}">
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
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>

<script>
    import DateField from '../../components/DateField.vue';
    import StorageLocationSelectionForm from '../../components/StorageLocationSelectionForm.vue';

    export default {
        components: {
            'datefield' : DateField,
            'storagelocationselectionform' : StorageLocationSelectionForm
        },
        props:[],
        data() {
            return {
                products: {},
                locations: [],
                filters: {
                    locationType: '',
                    location: {},
                    endingAt: moment().format('YYYY-MM-DD')
                },
                loading: false,
            };
        },
        created() {
            this.getLevels();
            console.log('Component mounted.')
        },
        methods: {
            doFilter: function(event) {
                this.products = {};
                this.getLevels();
            },
            getLevels: function() {
                this.loading = true;
                axios
                    .get('/api/stock-levels', { params: this.buildParams() })
                    .then(response => {
                        this.products = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            buildParams: function () {
                return {
                    locationType: this.filters.locationType,
                    location: this.filters.location.id || null,
                    endingAt: moment(this.filters.endingAt).endOf('day').toISOString()
                }
            }
        }
    }
</script>
