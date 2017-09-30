import Vue from 'vue'

export const state = {
    data: {
        save:false
    }
}

export const mutations = {
    set(state, params) {
        for (let key in params) Vue.set(state.data, key, params[key])
    },
    remove(state, param) {
        var index = state.data.indexOf(param);
        state.data.splice(index, 1)
    },
}

export const getters = {
    get(state) {
        return state.data
    },
};