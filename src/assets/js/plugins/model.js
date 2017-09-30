export function install(Vue) {
    Vue.prototype.$modal = {
        show(name, params) {
            Vue.prototype.$events.$emit('toggle', name, true, params)
        },

        hide(name, params) {
            Vue.prototype.$events.$emit('toggle', name, false, params)
        },

        toggle(name, params) {
            Vue.prototype.$events.$emit('toggle', name, undefined, params)
        }
    }
}