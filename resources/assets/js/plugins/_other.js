
// import Config from '../config'
import store from '../store/index'

export function install(Vue) {
    // Vue.prototype.$config = Config;
    Vue.prototype.$events = new Vue({});
    Vue.prototype.console = console.log.bind(this);
    Vue.myStore = Vue.prototype.$myStore = store
}

