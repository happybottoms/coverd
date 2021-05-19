<template>
    <modal
        id="clientBlukUpdateModal"
        classes="modal-info"
        :confirm-action="updateClients"
        :confirm-enabled="isAvailableConfirm"
    >
        <div
            v-if="userHasRole('ROLE_CLIENT_EDIT_ALL') && !isSameStatus"
            class="alert alert-warning"
        >
            <h4><i class="icon fas fa-exclamation-triangle"></i> Alert!</h4>
            Selected clients must all be in the same status to bulk change status.
        </div>

        <template slot="header">
            Update Clients
        </template>
        <optionliststatic
            v-if="userHasRole('ROLE_CLIENT_EDIT_ALL')"
            v-model="status"
            label="Status"
            display-property="title"
            :disabled="!isSameStatus"
            :preloaded-options="enabledClientStatus"
            empty-string="-- Status --"
        />

        <PartnerSelectionForm
            v-if="userHasRole('ROLE_CLIENT_MANAGE_OWN') || userHasRole('ROLE_CLIENT_EDIT_ALL')"
            v-model="partner"
            label="Assigned Partner"
            :options="allPartners"
        />
        <template slot="confirmButton">
            Update
        </template>
    </modal>
</template>

<script>
import Modal from "../../components/Modal.vue";
import PartnerSelectionForm from "../../components/PartnerSelectionForm";
import OptionListStatic from "../../components/OptionListStatic.vue";
import { mapGetters } from "vuex";

export default {
    name: "ClientMerge",
    components: {
        modal: Modal,
        PartnerSelectionForm,
        optionliststatic: OptionListStatic
    },
    props: {
        selectedClients: { type: Array, required: true }
    },
    data() {
        return {
            partner: { id: null },
            status: null,
            enabledClientStatus: []
        };
    },
    computed: {
        ...mapGetters(["allPartners", "userHasRole"]),
        isSameStatus: function() {
            let unique = new Set(this.selectedClients.map(client => client.status));
            return unique.size === 1;
        },
        isAvailableConfirm: function() {
            if (this.userHasRole("ROLE_CLIENT_EDIT_ALL")) {
                return !!this.status || !!this.partner.id;
            }
            return !!this.partner.id;
        }
    },
    methods: {
        updateClients: function() {
            let data = {
                partner: this.partner.id
            };
            if (this.userHasRole("ROLE_CLIENT_EDIT_ALL")) {
                data.status = this.status;
            }
            this.$emit("update-bulk-clients", data);
        },
        reset: function() {
            this.partner = { id: null };
            this.status = null;
        },
        getEnabledTransitions: function(clientId) {
            axios
                .get("/api/clients/" + clientId)
                .then(response => {
                    this.enabledClientStatus = (response.data.meta?.enabledTransitions || []).map(a => ({
                        ...a,
                        id: a.transition
                    }));
                })
                .catch(error => console.log("Error receiving clients %o", error));
        },
        fetchData: function() {
            if (this.userHasRole("ROLE_CLIENT_EDIT_ALL") && this.isSameStatus) {
                this.getEnabledTransitions(this.selectedClients[0].id);
            }
        }
    }
};
</script>
