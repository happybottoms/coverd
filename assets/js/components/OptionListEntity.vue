<template>
    <div class="form-group">
        <label
            v-if="label"
            v-text="label"
        />
        <select
            v-if="!groupProperty"
            v-model="value[property]"
            v-chosen
            class="form-control"
            @change="onChange"
        >
            <option
                value=""
                v-text="emptyOption"
            />
            <option
                v-for="item in options"
                :key="item.id"
                :selected="value.id === item.id"
                :value="item.id"
                v-text="item[displayProperty].title ? item[displayProperty].title : item[displayProperty]"
            />
        </select>
        <select
            v-else
            v-model="value[property]"
            v-chosen
            class="form-control"
            @change="onChange"
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
                    :selected="value[property] === item.id"
                    :value="item.id"
                    v-text="item[displayProperty]"
                />
            </optgroup>
        </select>
    </div>
</template>

<script>
    export default {
        name: 'OptionListEntity',
        props: {
            value: { type: Object },
            label: { type: [String, Boolean], default: false },
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
                loaded: false,
            }
        },
        computed: {
            options: function() {
                let self = this;
                let list = self.listOptions.length > 0 ? self.listOptions : self.preloadedOptions;

                // THIS IS THE PROBLEM!!!!!!!
                // if (this.alphabetize) {
                //     list = list.sort(function(a, b) {
                //         return a[self.displayProperty] > b[self.displayProperty] ? 1 : -1;
                //     })
                // }

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
            emptyOption: function() {
                return this.emptyString ? this.emptyString : '-- Select Item --';
            }
        },

        created() {
            var self = this;

            if (!self.value || !self.value.id) self.value.id = null;

            if (this.apiPath) {
                axios
                    .get('/api/' + this.apiPath)
                    .then(response => {
                        self.listOptions = response.data.data;
                        self.loaded = true;
                        self.$emit('loaded');
                });
            } else {
                self.listOptions = self.preloadedOptions;
                self.loaded = true;
                self.$emit('loaded');
            }
        },

        methods: {
            onChange: function(event) {
                this.$emit('change', event);
            }
        }
    }
</script>
