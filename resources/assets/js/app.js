
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vue from 'vue';
import Router from 'vue-router';
Vue.use(Router);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const debug = process.env.NODE_ENV !== 'production';

Vue.config.devtools = debug;
Vue.prototype.$debug = debug;
Vue.prototype.$events = new Vue({});

import App from './App.vue';
import editor from './pages/index.vue';
import queues from './pages/queues.vue';

// import axios from 'axios';
// Vue.prototype.$axios = axios.create({
//     baseURL: `http://redis-web.dev/api`,
//     headers: {
//         Authorization: 'Bearer {token}'
//     }
// });

// Vue.prototype.$mp = function(){};

const router = new Router({
    linkActiveClass: 'active',
    mode: 'history',
    routes: [
        { path: '/index', name: 'index', component: editor },
        { path: '/index', name: 'editor', component: editor },
        { path: '/queues', name: 'queues', component: queues },
    ],
});

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

