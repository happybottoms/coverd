<template>
    <section class="content">
        <div class="pull-right">
            <div class="btn-group">
                <button
                    type="button"
                    class="btn btn-default btn-flat"
                    @click.prevent="saveSort"
                >
                    <i class="fa fa-fw fa-save" />
                    Save Product Order
                </button>
            </div>
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
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <!--
                        <div class="box-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <TableSkeleton :loading="loading">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th />
                                        <th>Product ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <Draggable
                                    v-model="products.data"
                                    tag="tbody"
                                >
                                    <tr
                                        v-for="product in products.data"
                                        :key="product.id"
                                    >
                                        <td><i class="fa fa-arrows" /></td>
                                        <td>
                                            <router-link :to="{ name: 'product-edit', params: { id: product.id } }">
                                                <i class="fa fa-edit" />{{ product.id }}
                                            </router-link>
                                        </td>
                                        <td v-text="product.name" />
                                        <td v-text="product.productCategory.name" />
                                        <td v-text="product.orderIndex" />
                                        <td v-text="product.status" />
                                        <td>{{ product.updatedAt | dateTimeFormat }}</td>
                                    </tr>
                                </Draggable>
                            </table>
                        </TableSkeleton>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>

<script>
import Draggable from "vuedraggable";
import TableSkeleton from "../../components/skeleton/TableSkeleton";

export default {
    components: {
        Draggable,
        TableSkeleton
    },
    props: [],
    data() {
        return {
            products: {},
            loading: true
        };
    },
    created() {
        axios
            .get("/api/products")
            .then(response => (this.products = response.data))
            .catch(error => {
                console.log(error);
            })
            .finally(() => (this.loading = false));
        console.log("Component mounted.");
    },
    methods: {
        saveSort() {
            let me = this;
            let ids = this.products.data.map(product => product.id);
            axios
                .post("/api/products/order", {
                    ids: ids
                })
                .then(response => me.setProducts(response.data.data));
        },
        setProducts(products) {
            this.products.data = products.sort((a, b) => a.orderIndex - b.orderIndex);
        }
    }
};
</script>
