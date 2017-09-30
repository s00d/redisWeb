import Vue from 'vue'
export const state = {
    data: {}
}

export const mutations = {
    push(state, notification) {
        Vue.set(state.data, notification.id, notification)
    },
    remove(state, notification) {
        Vue.delete(state.data, notification.id)
    }
}