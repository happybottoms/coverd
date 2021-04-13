<template>
    <section class="content">
        <div class="pull-right">
            <button
                class="btn btn-success btn-flat"
                @click.prevent="save"
            >
                <i class="fa fa-save fa-fw" />Save Warehouse
            </button>
            <div class="btn-group">
                <button
                    type="button"
                    class="btn btn-default dropdown-toggle dropdown btn-flat"
                    data-toggle="dropdown"
                >
                    <span class="fa fa-ellipsis-v" />
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a
                            href="#"
                            @click.prevent="askDelete"
                        >
                            <i class="fa fa-trash fa-fw" />Delete Warehouse
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <h3 class="box-title">
            Edit Warehouse
        </h3>

        <div class="row">
            <form role="form">
                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="icon fa fa-industry fa-fw" />Warehouse Info
                            </h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- text input -->
                            <div class="form-group">
                                <label>Warehouse Name</label>
                                <input
                                    v-model="warehouse.title"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter warehouse name"
                                >
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    v-model="warehouse.status"
                                    class="form-control"
                                >
                                    <option value="ACTIVE">
                                        Active
                                    </option>
                                    <option value="INACTIVE">
                                        Inactive
                                    </option>
                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>

                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="icon fa fa-map-marker fa-fw" />Location
                            </h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- text input -->
                            <addressform v-model="warehouse.address" />
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

                <div class="col-md-6">
                    <template>
                        <div
                            v-for="contact in warehouse.contacts"
                            :key="contact.id"
                        >
                            <div
                                v-if="!contact.isDeleted"
                                class="box box-info"
                            >
                                <div class="box-header with-border">
                                    <button
                                        class="btn btn-xs btn-danger btn-flat pull-right"
                                        title="Remove Contact"
                                        @click.prevent="contact.isDeleted = true"
                                    >
                                        <i class="fa fa-trash fa-fw" />
                                    </button>
                                    <h3 class="box-title">
                                        <i class="icon fa fa-user fa-fw" />{{ contact.firstName }} {{ contact.lastName }}
                                    </h3>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <contact :contact="contact" />
                                </div>
                            <!-- /.box-body -->
                            </div>
                            <div
                                v-else
                                class="box box-danger bg-gray"
                            >
                                <div class="box-header">
                                    <button
                                        class="btn btn-xs btn-info btn-flat pull-right"
                                        title="Undo Delete"
                                        @click.prevent="contact.isDeleted = false"
                                    >
                                        <i class="fa fa-undo fa-fw" />
                                    </button>
                                    <h3 class="box-title">
                                        <i class="icon fa fa-trash fa-fw" />Marked for deletion: {{ contact.firstName }} {{ contact.lastName }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </template>
                    <button
                        class="btn btn-info btn-flat pull-right"
                        @click.prevent="warehouse.contacts.push({isDeleted: false})"
                    >
                        <i class="fa fa-plus fa-fw" /> Add another contact
                    </button>
                </div>
            </form>
        </div>
        <modal
            id="confirmModal"
            :confirm-action="this.deleteWarehouse"
            classes="modal-danger"
        >
            <template slot="header">
                Delete Warehouse
            </template>
            <p>Are you sure you want to delete <strong>{{ warehouse.title }}</strong>?</p>
            <template slot="confirmButton">
                Delete Warehouse
            </template>
        </modal>
    </section>
</template>


<script>
    import Modal from '../../components/Modal.vue';
    import AddressForm from '../../components/AddressFormFields.vue';
    import ContactFormField from '../../components/ContactFormFields.vue';

    export default {
        components: { 
            'modal' : Modal,
            'addressform' : AddressForm,
            'contact' : ContactFormField 
        },
        props: ['new'],
        data() {
            return {
                warehouse: {
                    address: {},
                    contacts: []
                }
            };
        },
        created() {
            var self = this;
            if (this.new) {
                this.warehouse.contacts.push({ isDeleted: false });
            } else {
                axios
                    .get('/api/warehouses/' + this.$route.params.id)
                    .then(response => self.warehouse = response.data.data);
            }
            console.log('Component mounted.')
        },
        methods: {
            save: function () {
                var self = this;
                if (this.new) {
                    axios
                        .post('/api/warehouses', this.warehouse)
                        .then(response => self.$router.push({ name: 'warehouses' }))
                        .catch(function (error) {
                            console.log(error);
                        });
                } else {
                    axios
                        .patch('/api/warehouses/' + this.$route.params.id, this.warehouse)
                        .then(response => self.$router.push({ name: 'warehouses' }))
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            askDelete: function() {
                $('#confirmModal').modal('show');
            },
            deleteWarehouse: function() {
                var self = this;
                axios
                    .delete('/api/warehouses/' + this.$route.params.id)
                    .then(self.$router.push({ name: 'warehouses' }));
            }
        }
    }
</script>
