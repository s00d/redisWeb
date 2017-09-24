import Axios from 'axios';
import config from '../config';


if(config.debug) Axios.defaults.headers.common['X-Requested-With'] = "XMLHttpRequest";

let token = document.querySelector('#csrf-token').getAttribute('value');
// Create new axios instance
let axios = Axios.create({
    baseURL: config.env.API_URL + (config.env.API_PREFIX || '/api'),
    timeout: 1000000,
    headers: {
        Authorization: 'Bearer ' + token
    }
});

console.log('baseURL', config.env.API_URL + (config.env.API_PREFIX || '/api'));

export const install = function (Vue, options) {
    Vue.axios = Vue.prototype.$axios = axios;
    Vue.prototype.$setToken = function (token) {
        axios.defaults.headers.common['Authorization'] = token ? 'Bearer ' + token : null
    };
};

