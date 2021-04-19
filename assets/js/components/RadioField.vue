<template>
    <div class="form-group">
        <label v-text="label" />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <div
            v-for="option in options"
            :key="option.id"
            class="radio"
        >
            <label>
                <input
                    v-model="value"
                    type="radio"
                    :value="option.id"
                    @change="$emit('input', $event.target.value)"
                >
                {{ displayText(option) }}
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'RadioField',
        props: {
            value: { type: String, required: true },
            label: { type: String },
            helpText: { type: String, requird: false, default: "" },
            displayProperty: { type: String, default: 'name'},
            displayTextFn: { type: Function },
            preloadedOptions: { type: Array, default: function() {return []}},
            alphabetize: { type: Boolean, default: true },
        },
        data() {
            return {
                listOptions: [],
            }
        },
        computed: {
            loaded: function() { return this.options.length > 0 },
            options: function() { return this.listOptions.length > 0 ? this.listOptions : this.preloadedOptions },
        },
        methods: {
            displayText: function(item) {
                if (this.displayTextFn) {
                    return this.displayTextFn(item);
                } else {
                    return item[this.displayProperty];
                }
            }
        },

    }
</script>