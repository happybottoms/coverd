<template>
    <div class="form-group">
        <label
            v-if="label"
            v-text="label"
        />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <input
            :value="value"
            type="text"
            class="form-control"
            :placeholder="placeholder"
            @input="$emit('input', $event.target.value)"
        >
    </div>
</template>

<script>
    export default {
        name: 'TextField',
        props: {
            label: { type: [String, Boolean], default: false },
            value: { type: String, required: false },
            placeholder: { type: String, required: false, default: "" },
            helpText: { type: String, required: false, default: "" },
        }
    }
</script>