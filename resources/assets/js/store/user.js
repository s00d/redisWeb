import Vue from 'vue'

export const state = {
    user: {},
    apikey: '',
    logined: false
};

export const mutations = {
    set(state, user) {
        Vue.set(state, 'user', user);
        state.apikey = user.apikey;
        state.logined = true;
    },
    exit(state) {
        Vue.set(state, 'user', {});
        state.apikey = '';
        state.logined = false;
    }
};


export const getters = {
    get_user_group(state) {
       return state.user.group;
    }
};