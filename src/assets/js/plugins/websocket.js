import IO from "socket.io-client";

let connection = ':2635';

let params = {
    reconnection: true,
    reconnectionDelay: 10000,
    reconnectionDelayMax: 20000,
    timeout: 1000
};

export function install (Vue) {
    // if (connection != null && typeof connection === "object") socket = connection;
    Vue.prototype.$socket = null;


    let addListeners = function() {
        if (this.$options["socket"]) {
            if(Vue.prototype.$socket !== null) return false;
            Vue.prototype.$socket = IO(connection || "", params);

            let conf = this.$options.socket;
            if (conf.namespace) {
                this.$socket = IO(conf.namespace, conf.options);
            }

            if (conf.events) {
                let prefix = conf.prefix || "";
                Object.keys(conf.events).forEach((key) => {
                    let func = conf.events[key].bind(this);
                    this.$socket.on(prefix + key, func);
                    conf.events[key].__binded = func;
                });
            }
        }
    };

    let removeListeners = function() {
        if (this.$options["socket"]) {
            let conf = this.$options.socket;

            if (conf.namespace) {
                this.$socket.disconnect();
            }

            if (conf.events) {
                let prefix = conf.prefix || "";
                Object.keys(conf.events).forEach((key) => {
                    this.$socket.off(prefix + key, conf.events[key].__binded);
                });
                Vue.prototype.$socket = null
            }
        }
    };

    Vue.mixin({
        beforeCreate: addListeners,
        beforeDestroy: removeListeners
    });

}
