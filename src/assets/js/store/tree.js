import Vue from 'vue'
import { omitBy } from 'lodash';

export const state = {
    data: {children: 'unload', open: true},
    filter: '*',
    searchKeyByKeys(keys, offset = 0, cancel_search = false) {
        let tree = this.data;
        if(!cancel_search) for(let i = 0; i < keys.length-offset; i++) {
            tree = tree.children[keys[i]];
        }
        console.log(tree);
        return tree;
    }
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
        let tree = state.searchKeyByKeys(keys, 1);
        Vue.delete(tree.children, keys[keys.length - 1]);
    },
    open(state, link) {
        let keys = link.split(":");
        let tree = state.searchKeyByKeys(keys);
        Vue.set(tree, 'open', 'open' in tree ? !tree.open : true);
        if(tree.children === 'unload') this.dispatch('tree/getTree', link);
    },
    edit(state, link) {
        let keys = link.split(":");
        let tree = state.searchKeyByKeys(keys);
        Vue.set(tree, 'edit', 'edit' in tree ? !tree.edit : true);
    },
    pushNew(state, link) {
        let keys = link.split(":");
        let tree = state.searchKeyByKeys(keys, 1, keys[0] === '');
        console.log(tree);
        if(tree.children === 'unload') Vue.set(tree, 'children', {});
        Vue.set(tree.children, keys[keys.length-1], {'name': keys[keys.length-1], 'link': link});
    },
    pushData(state, data = {link: '', add: {}}) {
        let keys = data.link.split(":");
        let tree = state.searchKeyByKeys(keys, 0, data.link === '');
        if(tree.children === 'unload') Vue.set(tree, 'children', {});
        Vue.set(tree, 'link', data.link);
        Vue.set(tree, 'name', data.add.name);
        Vue.set(tree, 'open', true);
        Vue.set(tree, 'children', data.add.children);
    },
    pushItem(state, data = {link: '', item: {}}) {
        let keys = data.link.split(":");
        let tree = state.searchKeyByKeys(keys, 0, keys.length === 1);

        if(tree.children === 'unload') Vue.set(tree, 'children', {});

        Vue.set(tree.children, data.item.name, data.item);
    },
    rename(state, links = {old_link: '', new_link: ''}) {
        let keys = links.old_link.split(":");
        let tree = state.searchKeyByKeys(keys);

        let item = JSON.parse(JSON.stringify(tree));
        item.link = links.new_link;
        item.name = links.new_link.split(":").slice(-1)[0];

        this.commit('tree/pushItem', {link: links.new_link, item: item});
        this.commit('tree/del', links.old_link);

        Vue.axios.post("/setName", Vue.mp_axios({key: links.old_link, new_name: links.new_link, old_name: links.old_link}) ) ;
    },
    dropItems(state, data = {link: "", fromKey: "", toKey: ""}) {
        let keys = data.link.split(":");
        let tree = state.searchKeyByKeys(keys, 1);

        let fromData = JSON.parse(JSON.stringify(tree.children[data.fromKey]));
        let toData = JSON.parse(JSON.stringify(tree.children[data.toKey]));

        Vue.set(tree.children, data.fromKey, toData);
        Vue.set(tree.children, data.toKey, fromData);
    }
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