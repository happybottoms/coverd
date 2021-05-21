<template>
    <div>
        <form
            role="form"
            class="row"
        >
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="icon fa fa-industry fa-fw" />Partner Info
                        </h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- text input -->
                        <div class="form-group">
                            <label>Partner Name</label>
                            <input
                                v-model="value.title"
                                type="text"
                                class="form-control"
                                placeholder="Enter partner name"
                            >
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select
                                v-model="value.partnerType"
                                class="form-control"
                            >
                                <option value="AGENCY">
                                    Agency
                                </option>
                                <option value="HOSPITAL">
                                    Hospital
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <optionlist
                                v-model="value.fulfillmentPeriod"
                                label="Fulfillment Period"
                                api-path="partners/fulfillment-periods"
                            />
                        </div>
                        <div class="form-group">
                            <optionlist
                                v-model="value.distributionMethod"
                                label="Distribution Method"
                                api-path="partners/distribution-methods"
                            />
                        </div>
                        <div class="form-group">
                            <label>Number of previous months to average for forecasting</label>
                            <input
                                v-model="value.forecastAverageMonths"
                                type="text"
                                class="form-control"
                                placeholder="3 (default)"
                            >
                        </div>
                        <div class="form-group">
                            <label>Legacy ID (Portal)</label>
                            <input
                                v-model="value.legacyId"
                                type="text"
                                class="form-control"
                            >
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
                        <AddressFormFields
                            v-model="value.address"
                        />
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>


            <div class="col-md-6">
                <template>
                    <div
                        v-for="contact in value.contacts"
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
                                <contact
                                    :contact="contact"
                                    :show-program-contact="true"
                                />
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
                    @click.prevent="value.contacts.push({isDeleted: false})"
                >
                    <i class="fa fa-plus fa-fw" />New contact
                </button>
            </div>
        </form>
    </div>
</template>


<script>
import AddressFormFields from '../../components/AddressFormFields.vue';
import ContactFormFields from '../../components/ContactFormFields.vue';
import OptionListEntity from '../../components/OptionListEntity.vue';

export default {
        name: 'PartnerLocationEditTab',
        components: {
            AddressFormFields,
            'contact' : ContactFormFields,
            'optionlist' : OptionListEntity
        },
        props: {
            new: { type: Boolean },
            value: { type: Object, required: true, default: function () {
                return {
                    address: {},
                    contacts: [],
                    fulfillmentPeriod: {},
                    distributionMethod: {},
                }
            }}
        },
        data() {
            return {
                partner: {
                    address: {},
                    contacts: [],
                    fulfillmentPeriod: {},
                    distributionMethod: {},
                    transition: '',
                }
            };
        },
        created() {
            if (this.new) {
                this.value.contacts.push({ isDeleted: false });
            }
        },
    }
</script>