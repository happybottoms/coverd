<template>
    <div class="form-group">
        <label v-text="label" />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <div class="input-group date">
            <div class="input-group-addon">
                <i class="far fa-calendar-alt" />
            </div>
            <input
                v-model.lazy="humanReadable"
                v-datepicker="{ format: format, tz: timezone }"
                type="text"
                class="form-control pull-right"
                @change="$emit('input', dateValue)"
            >
        </div>
    </div>
</template>

<script>
export default {
    name: "DateField",
    props: {
        value: { type: String, default: "" },
        label: { type: String, required: false, default: "Date:" },
        helpText: { type: String, required: false, default: "" },
        format: { type: String, default: "MM/DD/YYYY" },
        timezone: { type: String, required: false, default: "UTC" }
    },
    data() {
        return { dateValue: null };
    },
    computed: {
        humanReadable: {
            get: function() {
                if (!this.dateValue && !this.value) {
                    return;
                }
                let date = moment.tz(this.dateValue || this.value, this.timezone);
                return date.format(this.format);
            },
            set: function(val) {
                let date = moment.tz(val, this.format, this.timezone);
                this.dateValue = val ? date.format() : null;
            }
        }
    }
};
</script>
