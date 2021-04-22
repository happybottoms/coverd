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
                        <TableSkeleton :loading="loading">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Partner ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Fulfillment Period</th>
                                        <th>Distribution Method</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="partner in partners.data"
                                        :key="partner.id"
                                    >
                                        <td>
                                            <router-link :to="{ name: 'partner-edit', params: { id: partner.id } }">
                                                <i class="fa fa-edit" />{{ partner.id }}
                                            </router-link>
                                        </td>
                                        <td v-text="partner.title" />
                                        <td v-text="partner.partnerType" />
                                        <td>{{ partner.status | statusFormat }}</td>
                                        <td v-text="partner.fulfillmentPeriod.name" />
                                        <td
                                            v-text="partner.distributionMethod ? partner.distributionMethod.name : null"
                                        />
                                        <td>{{ partner.updatedAt | dateTimeFormat }}</td>
                                    </tr>
                                </tbody>
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
import TableSkeleton from '../../components/skeleton/TableSkeleton';

export default {
    components: {
        TableSkeleton
    },
    props: [],
    data() {
        return {
            partners: [],
            loading: true
        };
    },
    created() {
        axios
            .get("/api/partners")
            .then(response => (this.partners = response.data))
            .catch(error => {
                console.log(error);
            })
            .finally(() => (this.loading = false));
        console.log("Component mounted.");
    }
};
</script>
