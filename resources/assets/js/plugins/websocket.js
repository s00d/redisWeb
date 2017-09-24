import Echo from 'laravel-echo'

// io = require('socket.io-client');

export function install (Vue) {
    Vue.prototype.$Echo = new Echo({
        broadcaster: 'socket.io',
        key: '7865a610c264d00a35a17b03ca95b343',
        // host: window.location.hostname + ':6001'
        host: 'localhost:6001',
        namespace: ''
    });

    let addListeners = function() {
        let self = this;
        if (this.$options["socket"]) {
            let conf = this.$options.socket;
            if (conf.chanels) {
                let prefix = conf.prefix || "";
                Object.keys(conf.chanels).forEach((chanel_str) => {
                    let [type, chanel, listen] = chanel_str.split(".");
                    let func = conf.chanels[chanel_str].bind(self);
                    this.$Echo[type](prefix + chanel).listen(listen, func);
                    conf.chanels[chanel_str].__binded = func;
                });
            }
        }
    };

    let removeListeners = function() {
        if (this.$options["socket"]) {
            let conf = this.$options.socket;

            if (conf.chanels) {
                let prefix = conf.prefix || "";
                Object.keys(conf.chanels).forEach((chanel_str) => {
                    let [chanel, listen] = chanel_str.split(".")
                    this.$Echo.leave(prefix + chanel);
                });
            }
        }
    };

    Vue.mixin({
        beforeCreate: addListeners,
        beforeDestroy: removeListeners
    });

}