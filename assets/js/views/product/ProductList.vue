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
                        <TablePaged
                            :columns="columns"
                            api-url="/api/products"
                            edit-route="/products/"
                            :sort-order="[{ field: 'orderIndex', direction: 'asc' }]"
                            :per-page="50"
                        />
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>

<script>
    import TablePaged from "../../components/TablePaged";

    export default {
        components: {
            TablePaged
        },
        props:[],
        data() {
            return {
                loading: true,
                columns: [
                    { name: "__slot:link", title: "Product ID", sortField: "id" },
                    { name: "name", title: "Name", sortField: "name" },
                    { name: "productCategory.name", title: "Category", sortField: "pc.name" },
                    { name: "orderIndex", title: "Order", sortField: "orderIndex" },
                    { name: "status", title: "Status", sortField: "status" },
                    { name: "updatedAt", title: "Last Updated", callback: "dateTimeFormat", sortField: "updatedAt" }
                ]
            };
        },
        created() {
            console.log('Component mounted.');
        },
        methods: {
            saveSort() {
                // TO DO:
            },
        }
    }
</script>
