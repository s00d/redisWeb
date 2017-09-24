export function install(Vue) {
    const debug = Vue.config.devtools;
    setInterval(function () {
        try{
            if(!debug) console.clear();
        }catch (err) {}
    }, 1000)
}