<template>
    <modal
        id="clientTransferModal"
        classes="modal-info"
        :confirm-action="transferClient"
    >
        <template slot="header">
            Transfer Client
        </template>

        Transfer <strong>{{ client.fullName }}</strong> to <strong>{{ targetPartner.title }}</strong>?

        <template slot="confirmButton">
            <i class="fa fa-exchange-alt" /> Transfer
        </template>
    </modal>
</template>


<script>
    import Modal from '../../components/Modal.vue';

    export default {
        name: 'ClientTransferModal',
        components: {
            'modal' : Modal,
        },
        props: {
            client: { type: Object, required: true },
            targetPartner: { type: Object, required: true }
        },
        methods: {
            transferClient: function() {
                let me = this;
                axios
                    .post('/api/clients/'+this.client.id+'/transfer', )
                    .then(response => me.$router.push({name: 'client-edit', params: {id: response.data.data.id}}))
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        }
    }
</script>
