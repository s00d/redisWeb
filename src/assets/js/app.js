
require('./bootstrap');
window.Vue = require('vue');

import Vue from 'vue';

const debug = process.env.NODE_ENV !== 'production';

Vue.config.devtools = debug;
Vue.prototype.$debug = debug;
Vue.prototype.$events = new Vue({});

import App from './App.vue';


// import axios from 'axios';
// Vue.prototype.$axios = axios.create({
//     baseURL: `http://redis-web.dev/api`,
//     headers: {
//         Authorization: 'Bearer {token}'
//     }
// });

// Vue.prototype.$mp = function(){};
import router from './router'

let query = {};
router.beforeEach((to, from, next) => {
    query = to.query
    next();
});

Vue.prototype.$mp = function (params = {}) {
    let q = JSON.parse(JSON.stringify(query));
    for(let key in params) q[key] = params[key];
    return q
};

Vue.prototype.$mp_axios = function (params = {}) {
    let q = JSON.parse(JSON.stringify(query));
    if('db' in q) params['db'] = q['db'];
    if('server' in q) params['server'] = q['server'];
    return params
};

import store from './store/index';
if(debug) console.log('store', store);

import plugins from './plugins/index';
if(debug) console.log('plugins', plugins);

const app = new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App)
});

