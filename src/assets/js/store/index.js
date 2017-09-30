import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex);

let files;
let filenames = [];

try {
    files = require.context('~store', false, /^\.\/(?!index).*\.js$/)
    filenames = files.keys()
} catch (e) {
    console.warn('store error:', e.message)
}

function getModule(filename) {
    let file = files(filename);
    return file.default ? file.default : file
}

let store;
let storeData = {
    strict: process.env.NODE_ENV !== 'production',
    modules: []
};
for (let filename of filenames) {
    let name = filename.replace(/^\.\//, '').replace(/\.js$/, '');
    if (name === 'index') continue
    storeData.modules[name] = getModule(filename);
    storeData.modules[name].namespaced = true
    if( storeData.modules[name].hasOwnProperty('autoActions') ){
        let actions = storeData.modules[name].autoActions;
        if(typeof actions === "string") actions = Object.keys(storeData.modules[name][actions]);
        if( !storeData.modules[name].hasOwnProperty('actions') ) storeData.modules[name].actions = {};
        for (let i of actions) storeData.modules[name].actions[i] = ({ commit }, data) => commit(i, data);
        delete storeData.modules[name].actionsGen;
    }
}

export default store = new Vuex.Store(storeData);