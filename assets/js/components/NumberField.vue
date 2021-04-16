<template>
    <div class="form-group">
        <label v-text="label" />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <input
            :value="value"
            type="number"
            class="form-control"
            :placeholder="placeholder"
            @input="$emit('input', $event.target.value)"
        >
    </div>
</template>

<script>
    export default {
        name: 'NumberField',
        props: {
            value: { type: Number },
            label: { type: String, required: true },
            helpText: { type: String, requird: false, default: "" },
            placeholder: { type: [String,Number] },
        }
    }
</script>