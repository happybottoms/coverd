import axios from 'axios';

// initial state
const state = {
    all: [],
};

// getters
const getters = {
    allActiveStorageLocations: state => {
        let active = [...state.all].filter(storageLocation => storageLocation.status === "ACTIVE");
        let warehouses = active.filter(storageLocation => storageLocation.type === 'Warehouse').sort((a,b) => a.title < b.title ? -1 : 1);
        let partners = active.filter(storageLocation => storageLocation.type !== 'Warehouse').sort((a,b) => a.title.toLowerCase() < b.title.toLowerCase() ? -1 : 1);
        return [...warehouses, ...partners];
    },
    allActivePartners: state => {
        let active = [...state.all]
            .filter(storageLocation => storageLocation.status === "ACTIVE" && storageLocation.type !== 'Warehouse')
            .sort((a,b) => a.title.toLowerCase() < b.title.toLowerCase() ? -1 : 1);
        return active;
    },
    allActiveWarehouses: state => {
        let active = [...state.all]
            .filter(storageLocation => storageLocation.status === "ACTIVE" && storageLocation.type === 'Warehouse')
            .sort((a,b) => a.title.toLowerCase() < b.title.toLowerCase() ? -1 : 1);
        return active;
    },
    allAgencies: state => {
        return state.all.filter(storageLocation => storageLocation.partnerType == "AGENCY")
    },
    allHospitals: state => {
        return state.all.filter(storageLocation => storageLocation.partnerType == "HOSPITAL")
    },
    getStorageLocationById: (state) => (id) => {
        return state.all.find(storageLocation => storageLocation.id == id);
    }
};

// actions
const actions = {
    loadStorageLocations ({ commit }, force = false) {
        if ((state.all.length > 0 || state.loading) && !force) return;

        axios
            .get('/api/storage-locations')
            .then((response) => {
                commit('setStorageLocations', { list: response.data.data });
            },
                (err) => {
            console.log(err);
        });
    }
};

// mutations
const mutations = {
    setStorageLocations (state, { list }) {
        state.all = list;
    },
};

export default {
    state,
    getters,
    actions,
    mutations
};