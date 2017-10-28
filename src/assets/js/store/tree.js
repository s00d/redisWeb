import Vue from 'vue'
import { omitBy } from 'lodash';

export const state = {
    data: {children: 'unload', open: true},
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
        if(tree.children === 'unload') this.dispatch('tree/getTree', link);

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
    pushData(state, data = {link: '', add: {}}) {
        let keys = data.link.split(":");
        let tree = state.data;
        if(data.link !== '') for(let i = 0; i < keys.length; i++) {
            tree = tree.children[keys[i]];
        }
        Vue.set(tree, 'children', {});
        Vue.set(tree, 'link', data.link);
        Vue.set(tree, 'name', data.add.name);
        Vue.set(tree, 'open', true);
        Vue.set(tree, 'children', data.add.children);
    },
    pushItem(state, data = {link: '', item: {}}) {
        let keys = data.link.split(":");
        let tree = state.data;
        if(data.link !== '') for(let i = 0; i < keys.length; i++) {
            tree = tree.children[keys[i]];
        }
        if(tree.children === 'unload') {
            Vue.set(tree, 'children', {});
        }
        Vue.set(tree.children, data.item.name, data.item);
        // tree.children.push(data.item);
    },
    rename(state, links = {old_link: '', new_link: ''}) {
        let keys = links.old_link.split(":");
        let tree = state.data;
        for(let i = 0; i < keys.length; i++) {
            tree = tree.children[keys[i]];
        }

        this.commit('tree/pushData', {link: links.new_link, add: JSON.parse(JSON.stringify(tree))});
        this.commit('tree/del', links.old_link);
    },
    dropItems(state, data = {link: "", fromKey: "", toKey: ""}) {
        console.log(data.fromKey, data.toKey);
        let keys = data.link.split(":");
        let tree = state.data;
        for(let i = 0; i < keys.length - 1; i++) {
            tree = tree.children[keys[i]];
        }
        let fromData = JSON.parse(JSON.stringify(tree.children[data.fromKey]));
        let toData = JSON.parse(JSON.stringify(tree.children[data.toKey]));

        Vue.set(tree.children, data.fromKey, toData);
        Vue.set(tree.children, data.toKey, fromData);


    },
};

export const getters = {
    getFiltred(state) {
        return state.data
    }
};

export const actions = {
    getTree({ commit }, link = false) {
        let req = { paginate: false };
        if(link) req['link'] = link+':';
        req['filter'] = state.filter;
        Vue.axios.get('/getList', { params: Vue.mp_axios(req)}).then(response => {
            commit('pushData', {'link': ('link' in req) ? link : '', 'add': response.data.tree});
            // let timeout = 0;
            // for(let i in response.data.tree.children) {
            //     commit('pushItem', {link: link, item: response.data.tree.children[i]})
            //     setTimeout(() => {
            //         commit('pushItem', {link: link, item: response.data.tree.children[i]})
            //     }, timeout);
            //     timeout += 20;
            // }

        });
        // this.$store.dispatch('tree/getTree');
    }
};