import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

import index from '../pages/index.vue';
import queues from '../pages/queues.vue';

export default new Router({
    linkActiveClass: 'active',
    mode: 'history',
  routes: [
    // {
    //   path: '/',
    //   name: 'landing-page',
    //   component: require('@/components/LandingPage').default
    // },
      { path: '/', name: 'index', component: index },
      { path: '/queues', name: 'queues', component: queues },
      { path: '*', redirect: '/' }
  ]
})
