import axios from 'axios';

// initial state
const state = {
    all: [],
    loading: false,
};

// getters
const getters = {
    allClients: state => {
        return state.all
    },
    allActiveClients: state => {
        return state.all.filter(client => client.status == "ACTIVE")
    },
    getClientById: (state) => (id) => {
        return state.all.find(client => client.id == id);
    },
};

// actions
const actions = {
    loadClients ({ commit }, force = false) {
        if ((state.all.length > 0 || state.loading) && !force) return;

        state.loading = true;
        return new Promise((resolve, reject) => {
            axios
                .get('/api/clients/',  {
                    params: {per_page: -1}
                })
                .then((response) => {
                    commit('setClients', { list: response.data.data });
                    resolve(response);
                    state.loading = false;
                },
                (err) => {
                    reject(err);
            });
        });
    }
};

// mutations
const mutations = {
    setClients (state, { list }) {
        state.all = list;
    },
};

export default {
    state,
    getters,
    actions,
    mutations
};