<template>
    <div class="form-group">
        <label v-text="label" />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <textarea
            :value="value"
            class="form-control"
            @input="$emit('input', $event.target.value)"
        />
    </div>
</template>

<script>
    export default {
        name: 'TextareaField',
        props: {
            value: { type: String, required: true },
            label: { type: String, required: true },
            helpText: { type: String, requird: false, default: "" },
        }
    }
</script>