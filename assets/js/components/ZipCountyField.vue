<template>
    <div class="form-group">
        <OptionListEntity
            v-if="!multiple"
            ref="zipCountySelect"
            v-model="value"
            api-path="zip-county"
            display-property="label"
            empty-string="-- Select Zip Code --"
            :label="label"
            @input="onSelectionChange"
        />
        <MultiOptionListEntity
            v-else
            ref="zipCountySelect"
            v-model="value"
            api-path="zip-county"
            display-property="label"
            empty-string="-- Select Zip Code --"
            :label="label"
            @input="onSelectionChange"
        />
    </div>
</template>

<script>
    import OptionListEntity from './OptionListEntity.vue';
    import MultiOptionListEntity from "./MultiOptionListEntity";

    export default {
        name: 'ZipCountyField',
        components:{
            MultiOptionListEntity,
            OptionListEntity
        },
        props: {
            value: { required: false, type: [Object, Array] },
            label: { type: String, default: "Zip Code" },
            multiple: { type: Boolean, default: false },
            helpText: { type: String, required: false, default: "" },
        },
        mounted: function () {
            this.$refs.zipCountySelect.$on('change', eventData => this.onSelectionChange(eventData))
        },
        methods: {
            onSelectionChange: function (eventData) {
                this.$emit('change', eventData);
            }
        }
    }
</script>
