import { liteStorage } from "../../plugins/_liteStorage";

let params = {
    key: 'vuex',
    getState: (key, storage) => {
        let value = storage.get(key);
        try {
            return value && value !== 'undefined' ? JSON.parse(value) : undefined;
        } catch (err) {
            return undefined;
        }
    },
    setState: (key, state, storage) => {
        storage.set(key, state, 1000);
    },
    storage: liteStorage,
    filter: () => true,
    subscriber: (store) => (handler) => store.subscribe(handler)
};

export default function (ref = {}) {
    for(let i in ref) {
        params[i] = ref[i]
    }
    return store => {
        let savedState = params.getState(params.key, params.storage);
             if (typeof savedState === 'object') {
                 store.replaceState(_merge({}, store.state, savedState));
             }
        params.subscriber(store)(function(mutation, state) {
            if (params.filter(mutation)) params.setState(params.key, state, params.storage);
        })
    }
}
