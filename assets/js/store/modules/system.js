import axios from 'axios';

// initial state
const state = {
    user: null
};

// getters
const getters = {
    userHasRole: (state) => (role) => {
        if (state.user == null) return false;
        let roles = [];
        state.user.groups.forEach((group) => {
            roles.push(...group.roles);
        });
        return roles.includes(role);
    },
    userActivePartner: (state) => {
        if (state.user == null) return false;
        return state.user.activePartner || null
    },
    userPartners: (state) => {
        if (state.user == null) return false;
        return state.user.partners
    },
    isAdmin: (state) => {
        if (state.user == null) return false;
        return state.user.isAdmin
    },
    isPartner: (state) => {
        if (state.user == null) return false;
        return state.user.isPartner
    }
};

// actions
const actions = {
    async loadCurrentUser({ commit }) {
        if (state.user) {
            return;
        }
        await axios
            .get('/api/system/current-user', {
                params: {
                    include: ['groups', 'partners', 'activePartner']
                }
            })
            .then((response) => {
                commit('setUser', { user: response.data.data });
            }
            );
    },
    clearCurrentUser({ commit }) {
        commit('clearUser');
    }
};

// mutations
const mutations = {
    setUser(state, { user }) {
        state.user = user;
    },
    clearUser(state) {
        state.user = null;
    }
};

export default {
    state,
    getters,
    actions,
    mutations
};