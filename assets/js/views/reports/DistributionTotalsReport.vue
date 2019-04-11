<template>
    <section class="content">
        <div class="row">
            <h3 class="box-title col-lg-10">Distribution Totals Report</h3>
            <div class="col-lg-2 text-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-info btn-flat dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-fw fa-download"></i>Export
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a @click="downloadExcel"><i class="fa fa-fw fa-file-excel-o"></i>Excel</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 col-sm-4">
                <div class="form-group">
                    <label>Type</label>
                    <select class="form-control" v-model="filters.partnerType" v-chosen>
                        <option value="">--All Partner Types--</option>
                        <option value="AGENCY">Agency</option>
                        <option value="HOSPITAL">Hospital</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <hb-partnerselectionform
                    v-model="filters.partner"
                    label="Partner"
                ></hb-partnerselectionform>
            </div>

            <div class="form-group col-lg-3 col-sm-6">
                <hb-date v-model="filters.startingAt" label="Start Distribution Month" format="YYYY-MM-01" timezone="Etc/UTC"></hb-date>
            </div>
            <div class="form-group col-lg-3 col-sm-6">
                <hb-date v-model="filters.endingAt" label="End Distribution Month" format="YYYY-MM-01" timezone="Etc/UTC"></hb-date>
            </div>

            <div class="col-xs-1 text-right">
                <button class="btn btn-success btn-flat" @click="doFilter"><i class="fa fa-fw fa-filter"></i>Filter</button>
            </div>

        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <hb-tablepaged
                        :columns="columns"
                        :sortOrder="[{ field: 'p.id', direction: 'asc' }]"
                        :params="requestParams()"
                        ref="hbtable"
                        :perPage="50"
                        apiUrl="/api/reports/distribution-totals"
                    ></hb-tablepaged>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        props:[],
        data() {
            let columns = [
                { name: "id", title: "Partner ID", sortField: "p.id" },
                { name: "name", title: "Partner", sortField: "p.title" },
                { name: "type", title: "Partner Type", sortField: "p.partnerType" },
                { name: "total", title: "Total Distributed", sortField: "total", dataClass: "text-right", titleClass: "text-right" },
            ];

            return {
                transactions: {},
                locations: [],
                columns: columns,
                filters: {
                    partner: {},
                    partnerType: null,
                    endingAt: null,
                    startingAt: null,
                },
            };
        },
        methods: {
            requestParams: function () {
                return {
                    partner: this.filters.partner.id || null,
                    partnerType: this.filters.partnerType || null,
                    startingAt: this.filters.startingAt ? moment.tz(this.filters.startingAt, 'Etc/UTC').startOf('day').toISOString() : null,
                    endingAt: this.filters.endingAt ? moment.tz(this.filters.endingAt, 'Etc/UTC').endOf('day').toISOString() : null,
                }
            },
            doFilter () {
                this.$events.fire('filter-set', this.requestParams());
            },
            downloadExcel () {
                let params = this.requestParams();
                params.download = 'xlsx';
                axios.get('/api/reports/distribution-totals', { params: params, responseType: 'blob' })
                    .then(response => {
                        let filename = response.headers['content-disposition'].match(/filename="(.*)"/)[1]
                        fileDownload(response.data, filename, response.headers['content-type'])
                    });
            }
        },
        mounted() {
            let me = this;
            this.$store.dispatch('loadProducts').then((response)=>{
                let newColumns = [];
                me.$store.getters.allOrderableProducts.forEach(function(product) {
                    newColumns.push(
                        { name: product.sku, title: product.name, sortField: "total" + product.id, dataClass: "text-right", titleClass: "text-right" }
                    );
                });
                me.columns.splice(-1, 0, ...newColumns);
                me.$refs.hbtable.reinitializeFields();
            });
            console.log('Component mounted.')
        }
    }
</script>