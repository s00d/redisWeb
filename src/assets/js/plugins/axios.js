import Axios from 'axios';

const debug = process.env.NODE_ENV !== 'production';

if(debug) Axios.defaults.headers.common['X-Requested-With'] = "XMLHttpRequest";

// Create new axios instance
let axios = Axios.create({
    baseURL: '/api',
    timeout: 1000000,
});

console.log('baseURL', '/api');

export const install = function (Vue, options) {
    Vue.axios = Vue.prototype.$axios = axios;
    Vue.prototype.$setToken = function (token) {
        axios.defaults.headers.common['Authorization'] = token ? 'Bearer ' + token : null
    };
};

