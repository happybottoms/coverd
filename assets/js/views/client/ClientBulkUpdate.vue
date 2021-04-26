<template>
    <modal
        id="clientBlukUpdateModal"
        classes="modal-info"
        :confirm-action="updateClients"
        :confirm-enabled="isAvailableConfirm"
    >
        <template slot="header">
            Update Clients
        </template>
        <optionliststatic
            v-if="userHasRole('ROLE_CLIENT_EDIT_ALL') && isChangableStatus"
            v-model="status"
            label="Status"
            display-property="title"
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
        isChangableStatus: function() {
            if (this.selectedClients.length <= 1) return false;
            return this.selectedClients.map(a => a.status).filter((c, i, t) => t.indexOf(c) === i).length === 1;
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
            if (this.userHasRole("ROLE_CLIENT_EDIT_ALL") && this.isChangableStatus) {
                this.getEnabledTransitions(this.selectedClients[0].id);
            }
        }
    }
};
</script>
