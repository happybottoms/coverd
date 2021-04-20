<template>
    <modal
        id="clientTransferModal"
        classes="modal-info"
        :confirm-action="transferClient"
    >
        <template slot="header">
            Transfer Client
        </template>

        <template>
            <div class="content_area">
                <span>Transfer <strong>{{ client.fullName }}</strong> to</span>
                <optionliststatic
                    v-model="targetPartner"
                    class="option_partners"
                    :preloaded-options="partners"
                    empty-string="-- Select Partner --"
                />
                ?
            </div>
        </template>

        <template slot="confirmButton">
            <i class="fa fa-exchange-alt" /> Transfer
        </template>
    </modal>
</template>

<script>
import Modal from "../../components/Modal.vue";
import OptionListStatic from "../../components/OptionListStatic.vue";

export default {
    name: "ClientTransferModal",
    components: {
        modal: Modal,
        optionliststatic: OptionListStatic
    },
    props: {
        client: { type: Object, required: true },
        targetPartners: { type: Array, required: true }
    },
    data() {
        return {
            targetPartner: null
        };
    },
    computed: {
        partners: function() {
            return this.targetPartners ? this.targetPartners.map(p => ({ id: p.id, name: p.title })) : [];
        }
    },
    methods: {
        transferClient: function() {
            let me = this;
            if (this.targetPartner) {
                console.log(this.targetPartner);
            }
            // axios
            //     .post('/api/clients/'+this.client.id+'/transfer', )
            //     .then(response => me.$router.push({name: 'client-edit', params: {id: response.data.data.id}}))
            //     .catch(function (error) {
            //         console.log(error);
            //     });
        }
    }
};
</script>

<style scoped>
.content_area {
    display: flex;
    align-items: center;
}
.option_partners {
    margin: 0 0.3em;
}
</style>
