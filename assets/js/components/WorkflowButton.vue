<template>
    <div class="pull-left">
        <button
            class="btn btn-info btn-flat dropdown-toggle"
            data-toggle="dropdown"
        >
            <i class="fa fa-info-circle" /> {{ status | statusFormat }}
        </button>
        <ul
            v-if="workflow.enabledTransitions"
            class="dropdown-menu dropdown-menu-right"
        >
            <li
                v-for="enabledTransition in getSortedTransitions()"
                :key="enabledTransition.transition"
            >
                <a
                    @click.prevent="doTransition(enabledTransition.transition)"
                >
                    <i class="fa fa-arrow-circle-right" />{{ enabledTransition.title }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        entityApi: { type: String, required: true },
        status: { type: String, required: true },
        workflow: { type: Object, required: true },
    },
    methods: {
        doTransition: function(transition) {
            let self = this;
            axios.patch(self.entityApi + '/' + this.$route.params.id + '/transition', {'transition': transition})
                .then(response => {
                    self.status = response.data.data.status;
                    self.workflow = response.data.meta;
                });
        },
        getSortedTransitions() {
            const inputObject = this.workflow.enabledTransitions;

            return Object
                .keys(inputObject)
                .map((key) => inputObject[key])
                .sort(function (a, b) {
                        let titleA = a.title.toUpperCase();
                        let titleB = b.title.toUpperCase();

                        if (titleA < titleB) {
                            return -1;
                        }

                        if (titleA > titleB) {
                            return 1;
                        }

                        return 0;
                    }
                );
        }
    }
}
</script>

<style scoped>

</style>