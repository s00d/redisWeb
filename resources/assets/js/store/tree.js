import Vue from 'vue'
import { omitBy } from 'lodash';

export const state = {
    data: {},
    filter: '*'
};

export const mutations = {
    set(state, tree) {
        Vue.set(state, 'data', tree);
    },
    setFilter(state, filter) {
        Vue.set(state, 'filter', filter);
    },
    del(state, link) {
        let keys = link.split(":");
        let tree = state.data;
        let key_del = 0;
        for(let i = 0; i < keys.length-1; i++) {
            key_del = i+1;
            tree = tree.children[keys[i]];
            if(!(keys[i] in tree.children) || Object.keys(tree.children[keys[i]].children).length <= 1) break;
        }
        Vue.delete(tree.children, keys[key_del]);
    },
    open(state, link) {
        let keys = link.split(":");
        let tree = state.data;
        for(let i = 0; i < keys.length; i++) {
            tree = tree.children[keys[i]];
        }
        Vue.set(tree, 'open', 'open' in tree ? !tree.open : true);

    },
    pushNew(state, link) {
        let keys = link.split(":");
        let tree = state.data;
        let link_set = '';
        for(let i = 0; i < keys.length; i++) {
            link_set = link_set + ((link_set === '') ? '' :  ':') + keys[i];
            if (!('children' in tree)) Vue.set(tree, 'children', {});
            if (!(keys[i] in tree.children)) Vue.set(tree.children, keys[i], {'name': keys[i], 'link': link_set});
            tree = tree.children[keys[i]];
        }
    },
    pushData(state, data = {link: '', add: false}) {
        let keys = data.link.split(":");
        let tree = state.data;
        for(let i = 0; i < keys.length-1; i++) {
            tree = tree.children[keys[i]];
        }
        console.log(tree);
        Vue.set(tree.children, keys[keys.length - 1], data.add);
    },
    rename(state, links = {old_link: '', new_link: ''}) {
        let keys = links.old_link.split(":");
        let tree = state.data;
        for(let i = 0; i < keys.length; i++) {
            tree = tree.children[keys[i]];
        }

        this.commit('tree/push', {link: links.new_link, add:JSON.parse(JSON.stringify(tree))});
        this.commit('tree/del', links.old_link);
    },

};

export const getters = {
    getFiltred(state) {
        let filter = state.filter.replace('*','').toLowerCase();
        if (filter === '') return state.data;
        return {children: omitBy(state.data.children, (val, key) => val.link.toLowerCase().indexOf(filter) === -1)};
    }
};

export const actions = {
    getTree({ commit }) {
        Vue.axios.get('/api').then(response => {
            commit('tree/set', response.data.tree);
        });
        // this.$store.dispatch('tree/getTree');
    }
};