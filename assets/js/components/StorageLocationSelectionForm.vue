<template>
    <div>
        <div class="form-group">
            <optionlist
                v-if="editable"
                ref="storageLocationSelect"
                v-model="value"
                :label=label
                :preloaded-options="allActiveStorageLocations"
                display-property="title"
                group-property="type"
                empty-string="-- Select Storage Location --"
                :alphabetize="false"
                @change="onSelectionChange"
            />
            <span
                v-else
                v-text="value.title"
            />
        </div>
        <div
            v-if="!editable"
            class="form-group"
        >
            <address-view v-model="value.address" />
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import Address from '../components/AddressView.vue';
    import OptionListEntity from './OptionListEntity.vue';
    export default {
        components: {
            'address-view': Address,
            'optionlist': OptionListEntity
        },
        props: {
            value: { required: true, type: Object },
            editable: { type: Boolean, default: true },
            v: { type: Object },
            label: { type: String, default: 'Storage Location:' }
        },
        computed: mapGetters([
            'allActiveStorageLocations'
        ]),
        mounted: function () {
            this.$store.dispatch('loadStorageLocations');
            this.$refs.storageLocationSelect.$on('change', eventData => this.onSelectionChange(eventData))
        },
        methods: {
            onSelectionChange: function (eventData) {
                let currentPartner = this.$store.getters.getStorageLocationById(eventData.currentTarget.value);
                this.$emit('change', eventData);
            }
        }
    }
</script>
