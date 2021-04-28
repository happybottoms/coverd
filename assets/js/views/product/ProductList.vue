<template>
    <section class="content">
        <div class="pull-right">
            <div class="btn-group">
                <router-link
                    :to="{ name: 'product-new' }"
                    class="btn btn-success btn-flat pull-right"
                >
                    <i class="fa fa-plus-circle fa-fw" />
                    Create Product
                </router-link>
            </div>
        </div>
        <h3 class="box-title">
            Products List
        </h3>

        <div class="row">
            <div class="col-xs-2">
                <TextField
                    v-model="filters.name"
                    label="Name"
                />
            </div>
            <div class="col-xs-2">
                <OptionListStatic
                    v-model="filters.category"
                    label="Category"
                    :preloaded-options="categories"
                    empty-string="-- All Category --"
                />
            </div>

            <div class="col-xs-2">
                <OptionListStatic
                    v-model="filters.status"
                    label="Status"
                    :preloaded-options="statuses"
                    empty-string="-- All Statuses --"
                />
                <!-- /.input group -->
            </div>
            <div class="col-xs-3">
                <button
                    class="btn btn-success btn-flat"
                    @click="doFilter"
                >
                    <i class="fa fa-fw fa-filter" />
                    Filter
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <TablePaged
                        ref="hbtable"
                        :columns="columns"
                        api-url="/api/products"
                        edit-route="/products/"
                        :sort-order="[{ field: 'orderIndex', direction: 'asc'}]"
                        :params="requestParams()"
                        :per-page="50"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import TextField from "../../components/TextField";
    import OptionListStatic from '../../components/OptionListStatic.vue';
    import TablePaged from '../../components/TablePaged.vue';

    export default {
        components: {
            TextField,
            OptionListStatic,
            TablePaged
        },
        props:[],
        data() {
            return {
                filters: {
                    category: null,
                    status: "",
                    name: null
                },
                statuses: [
                    {id: "ACTIVE", name: "Active"},
                    {id: "INACTIVE", name: "Inactive"},
                ],
                categories: [],
                columns: [
                    { name: '__slot:link', title: "Product Id", sortField: 'id' },
                    { name: "name", title: "Name", sortField: "name" },
                    { name: "productCategory.name", title: "Category", sortField: "productCategory.name" },
                    { name: "orderIndex", title: "Order", sortField: "orderIndex" },
                    { name: "status", title: "Status", sortField: "status" },
                    { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
                ]
            };
        },
        created() {
            axios
                .get('/api/product-categories')
                .then(response => {
                    const {data} = response.data
                    this.categories = data || []
                })
                .catch(error => {
                    console.log(error)
                })
        },
        methods: {
            doFilter () {
                console.log('doFilter:', this.requestParams());
                this.$events.fire('filter-set', this.requestParams());
            },
            requestParams: function () {
                const params = {
                    status: this.filters.status || null,
                    category: this.filters.category || null,
                    name: this.filters.name || null
                }
                return params
            },
        }
    }
</script>
