<template>
    <div class="form-group">
        <label v-text="label" />
        <i
            v-if="helpText"
            v-tooltip
            :title="helpText"
            class="attribute-help-text fa fa-question-circle"
        />
        <select
            v-if="!groupProperty"
            v-chosen
            :value="value"
            class="form-control"
            :class="{'loaded': loaded}"
            @change="$emit('input', $event.target.value)"
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
                v-text="item[displayProperty]"
            />
        </select>
        <select
            v-else
            v-chosen
            :value="value"
            class="form-control"
            :class="{'loaded': loaded}"
            @change="$emit('input', $event.target.value)"
        >
            <option
                value=""
                v-text="emptyOption"
            />
            <optgroup
                v-for="group in options"
                :key="group.id"
                :label="label"
            >
                <option
                    v-for="item in group"
                    :key="item.id"
                    :selected="value == item.id"
                    :value="item.id"
                    v-text="item[displayProperty]"
                />
            </optgroup>
        </select>
    </div>
</template>

<script>
    export default {
        name: "OptionListApi",
        props: {
            value: { type: String, default: () => "" },
            label: { type: String },
            helpText: { type: String, requird: false, default: "" },
            apiPath: { type: String },
            preloadedOptions: { type: Array, default: function() {return []}},
            displayProperty: { type: String, default: 'name'},
            property: { type: String, default: 'id' },
            groupProperty: { type: String, default: null },
            emptyString: { type: String },
            alphabetize: { type: Boolean, default: true },
        },
        data() {
            return {
                listOptions: [],
            }
        },
        computed: {
            loaded: function() { return this.options.length > 0 },
            options: function() {
                var self = this;
                let list = self.listOptions.length > 0 ? self.listOptions : self.preloadedOptions;

                if (this.alphabetize) {
                    list = list.sort(function(a, b) {
                        return a[self.displayProperty] > b[self.displayProperty] ? 1 : -1;
                    })
                }

                if (this.groupProperty) {
                    let groupings = {};
                    list.forEach( function(item) {
                        if (!groupings[item[self.groupProperty]]) {
                            groupings[item[self.groupProperty]] = [];
                        }
                        groupings[item[self.groupProperty]].push(item);
                    });
                    list = groupings;
                }
                return list;
            },
            emptyOption: function() { return this.emptyString ? this.emptyString : '-- Select Item --'}
        },

        created() {
            var self = this;

            if (this.apiPath) {
                axios
                    .get('/api/' + this.apiPath)
                    .then(response => {
                        self.listOptions = response.data.data;
                        self.$emit('loaded');
                });
            } else {
                self.listOptions = self.preloadedOptions;
            }
        },

        methods: {
        }
    }
</script>
