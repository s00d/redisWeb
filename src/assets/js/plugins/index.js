import Vue from 'vue'

let files;
let filenames = [];
let plugins = {};

try {
    files = require.context('~plugins', false, /^\.\/(?!index).*\.js$/);
    filenames = files.keys()
} catch (e) {
    console.warn('store error:', e.message)
}

function getModule (filename) {
    if (filename === './index.js') return false;
    let file = files(filename);
    return file.install ? file.install : false
}

let index = 0;
for (let filename of filenames) {
    const plugin = {
        name: filename.replace(/^\.\//, '').replace(/\.js$/, ''),
        time_init: Math.floor(Date.now() / 1000),
        index: index++,
        install: getModule(filename)
    };
    if( plugin.hasOwnProperty('init') ) plugin.init();
    if(plugin.install) {
        Vue.use(plugin);
        plugins[plugin.name] = plugin;
    }
}

export default plugins