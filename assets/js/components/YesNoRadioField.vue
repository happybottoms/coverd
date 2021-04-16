<template>
    <div class="form-group">
        <label
            class="control-label"
            v-text="label"
        />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <div class="radio-inline">
            <label>
                <input
                    v-model="value"
                    type="radio"
                    :value="true"
                    @change="$emit('input', true)"
                >
                Yes
            </label>
        </div>
        <div class="radio-inline">
            <label>
                <input
                    v-model="value"
                    type="radio"
                    :value="false"
                    @change="$emit('input', false)"
                >
                No
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'YesNoRadioField',
        props: {
            value: { type: Boolean, required: true },
            label: { type: String, required: true },
            helpText: { type: String, required: false, default: "" },
        }
    }
</script>