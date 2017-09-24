
// import Config from '../config'
import store from '../store/index'

export function install(Vue) {
    // Vue.prototype.$config = Config;
    Vue.prototype.$events = new Vue({});
    Vue.prototype.console = console.log.bind(this);
    Vue.prototype.$type = Vue.config.app_type;
    Vue.myStore = Vue.prototype.$myStore = store

    Vue.prototype.$is_null = function (val) {
        switch (val) {
            case "":
            case 0:
            case "0":
            case null:
            case false:
            case typeof this == "undefined":
                return true;
            default:
                return false;
        }
    };
}

