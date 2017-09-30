class diversInstance{
    constructor(){
        this.selectDriver = 'cookie';
    }

    static get drivers(){
        let arr = {};
        try { arr.localStorage = localStorage; } catch (err) {
            try { arr.localStorage = window.localStorage; } catch (err) {}
        }
        try {
            arr.sessionStorage = sessionStorage;
        } catch (err) {
            try { arr.sessionStorage = window.sessionStorage; } catch (err) {}
        }
        arr.cookie = {
            getItem: function (name) {
                if (!name) return undefined;
                let matches = document.cookie.match(
                    new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)")
                );
                return matches ? decodeURIComponent(matches[1]) : undefined;
            },
            setItem: function (name, value, options = {}) {
                let expires = options.expires;

                if (typeof expires === "number" && expires) {
                    let d = new Date();
                    d.setTime(d.getTime() + expires * 1000);
                    expires = options.expires = d;
                }
                if (expires && expires.toUTCString) {
                    options.expires = expires.toUTCString();
                }

                let updatedCookie = name + "=" + value;

                for (let propName in options) {
                    updatedCookie += "; " + propName;
                    let propValue = options[propName];
                    if (propValue !== true) {
                        updatedCookie += "=" + propValue;
                    }
                }

                document.cookie = updatedCookie;
                return true;
            },
            removeItem: function (name) {
                this.setItem(name, "", {
                    expires: -1
                })
            },
            hasItem: function (name) {
                if (!name) return false;
                return (new RegExp(
                    "(?:^|;\\s*)" + name.replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\="
                )).test(document.cookie);
            },
            keys: function () {
                let aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
                for (let nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
                return aKeys;
            }
        };
        arr.virtual = {
            data: {},
            getItem: function (name) {
                return this.data[name];
            },
            setItem: function (name, value) {
                this.data[name] = value;
            },
            removeItem: function (name) {
                delete this.data[name];
            }
        };
        return arr;
    }
    static checkLocalStorage(storage) {
        if (typeof storage !== 'undefined') {
            try {
                storage.setItem('storage_test', 'yes');
                console.log('getItem', storage.getItem('storage_test'));
                if (storage.getItem('storage_test') === 'yes') {
                    storage.removeItem('storage_test');
                    return true;
                }
            } catch (e) {
                console.log(e);
            }
        }
        return false;
    }
    static checkDrivers(){
        for (let id in this.drivers) {
            if(this.checkLocalStorage(this.drivers[id])) {
                this.selectDriver = id;
                return this;
            }
        }
    }
    static get getDriverName() {
        return this.selectDriver;
    }
    static get getDriver() {
        return this.drivers[this.selectDriver]
    }
}

let driver = diversInstance.checkDrivers().getDriver;
let ttl = false;
let prefix = "ls_";

export const liteStorage = {
    convertToInteger: (str, default_value) => (/^\+?\d+$/.test(str)) ? parseInt(str) : default_value,
    isEmpty: (str) =>  (str === null || str === undefined),
    set: function (name, value, seconds = ttl) {
        try {
            driver.setItem(prefix + name.toString(),  JSON.stringify({
                value: value,
                expires_at: seconds ? Math.round(new Date()/1000) + seconds : false
            }));
            return true;
        } catch(e) {}
        return false;
    },
    get: function (name, default_value = false) {
        try {
            let data = JSON.parse(driver.getItem(prefix + name.toString()));

            if(this.isEmpty(data)) return default_value;
            else {
                if(data.expires_at && data.expires_at < Math.round(new Date()/1000)) liteStorage.remove(name);

                //if(this.isNormalInteger(val)) return parseInt(val);
                if(data.value === "false") return false;
                if(data.value === "true") return true;
                return data.value;
            }
        } catch(e) {}
        return default_value;
    },
    remove: function (name) {
        try {
            driver.removeItem(prefix + name.toString());
            return true;
        } catch(e) {}
        return false;
    },

    setItem: (name, value) => liteStorage.set(name, value),
    getItem: (name) => liteStorage.get(name),
    removeItem: (name) => liteStorage.remove(name),

    setDriver: (newDriver) => {
        driver = newDriver;
        return liteStorage;
    },
    rewriteDriver: () => {
        driver = diversInstance.checkDrivers().getDriver;
        return liteStorage;
    },
    getDriver: () => {
        return driver;
    }
};

export default function (params = {}) {
    if ('ttl' in params) ttl = params.ttl;
    if ('prefix' in params) prefix = params.prefix;

    return liteStorage
}

export function install(Vue) {
    Vue.liteStorage = Vue.prototype.$liteStorage = liteStorage
}

