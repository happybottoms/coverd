<template>
    <section class="content">
        <router-link
            :to="{ name: 'partner-new' }"
            class="btn btn-success btn-flat pull-right"
        >
            <i class="fa fa-plus-circle fa-fw" />Create Partner
        </router-link>
        <h3 class="box-title">
            Partner List
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
                        <table-paged
                            :columns="columns"
                            api-url="/api/partners"
                            edit-route="partners/"
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
import TablePaged from '../../components/TablePaged.vue';

export default {
    components: {
        TablePaged
        },
        data() {
            return {
            columns: [
                { name: '__checkbox', title: "#" },
                { name: '__slot:link', title: "Partner Id", sortField: 'id' },
                { name: 'title', title: "Title", sortField: 'title' },
                { name: 'partnerType', title: "Type", sortField: 'partnerType' },
                { name: 'status', title: "Status", sortField: 'status' },
                { name: 'fulfillmentPeriod.name', title: "Fulfillment Period", sortField: 'fulfillmentPeriod' },
                { name: 'distributionMethod.name', title: "Distribution Method", sortField: 'distributionMethod' },
                { name: 'updatedAt', title: "Last Updated", callback: 'dateTimeFormat', sortField: 'updatedAt' },
            ]
            };
        }
    }
</script>
