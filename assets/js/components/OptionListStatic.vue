<template>
    <div class="form-group">
        <label
            v-if="label"
            v-text="label"
        />
        <template v-if="chosen && loaded">
            <select
                v-chosen
                :value="value"
                class="form-control"
                :class="{ loaded: loaded }"
                @change="onChange"
            >
                <option
                    value=""
                    v-text="emptyOption"
                />
                <option
                    v-for="item in options"
                    :key="item.id"
                    :selected="value == item.id"
                    :value="item.id"
                    v-text="displayText(item)"
                />
            </select>
        </template>
        <template v-else-if="!chosen && loaded">
            <select
                :value="value"
                class="form-control"
                :class="{ loaded: loaded }"
                @change="onChange"
            >
                <option
                    value=""
                    v-text="emptyOption"
                />
                <option
                    v-for="item in options"
                    :key="item.id"
                    :selected="value == item.id"
                    :value="item.id"
                    v-text="displayText(item)"
                />
            </select>
        </template>
        <template v-else>
            <select
                class="form-control"
                disabled
            >
                <option selected>
                    Loading...
                </option>
            </select>
        </template>
    </div>
</template>

<script>
export default {
    name: "OptionListStatic",
    props: {
        value: { type: [Number, String] },
        property: { type: String },
        label: { type: [String, Boolean], default: false },
        preloadedOptions: {
            type: Array,
            default: function () {
                return [];
            },
        },
        displayProperty: { type: String, default: "name" },
        displayTextFn: { type: Function },
        emptyString: { type: String },
        alphabetize: { type: Boolean, default: true },
        chosen: { type: Boolean, default: true },
    },

    data() {
        return {
            listOptions: [],
        };
    },

    computed: {
        loaded: function () {
            return this.options.length > 0;
        },
        options: function () {
            return this.listOptions.length > 0 ? this.listOptions : this.preloadedOptions;
        },
        emptyOption: function () {
            return this.emptyString ? this.emptyString : "-- Select Item --";
        },
    },

    methods: {
        displayText: function (item) {
            if (this.displayTextFn) {
                return this.displayTextFn(item);
            } else {
                return item[this.displayProperty];
            }
        },
        onChange: function ($event) {
            this.$emit("input", $event.target.value);
        },
    },
};
</script>
