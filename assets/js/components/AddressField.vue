<template>
    <div class="box box-default box-solid">
        <div class="box-header with-border">
            <div
                class="box-title"
                v-text="label"
            />
        </div>
        <div class="box-body">
            <AddressFormFields
                v-model="value"
                :has-title="false"
            />
        </div>
    </div>
</template>

<script>
    import AddressFormFields from "./AddressFormFields";

    export default {
        name: "AddressField",
        components: {
            AddressFormFields,
        },
        props: {
            label: { type: String, required: false },
            value: { type: Object, required: true },
        },
    };
</script>
