<template>
    <transition appear name="slide-right" mode="out-in">
        <div class="container" v-if="show || key === ''">
            <info_component v-if="key === ''"></info_component>

            <div class="container" v-else>
                <button @click="deleteClick" class="btn btn-danger"><i class="glyphicon glyphicon-remove-sign"></i> Delete</button>
                <a target="_blank" :href="'/api/export?db=' + db+'&key='+key" class="btn btn-info"><i class="glyphicon glyphicon-export"></i> Export</a>
                <a target="_blank" :href="'/api/export?db=' + db+'&key='+key+'&type=json'" class="btn btn-info"><i class="glyphicon glyphicon-export"></i> Export JSON</a>
                <hr>


                <table class="table" style="width: 400px">
                    <tr>
                        <th width="70px">Name</th>
                        <th>Value</th>
                        <th width="50px">&nbsp;</th>
                        <th width="50px">&nbsp;</th>
                    </tr>
                    <title-editor v-model="params.key" :key_item="key"></title-editor>
                    <tr>
                        <td>Type:</td>
                        <td><div v-text="params.type"></div></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <ttl-editor v-model="params.ttl" :key_item="key"></ttl-editor>


                    <tr v-if="params.encoding">
                        <td>Encoding:</td>
                        <td><div v-text="params.encoding"></div></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>Size:</td>
                        <td><span v-text="params.size"></span> <span v-text="params.type == 'string' ? 'characters' : 'items'"></span></td>
                        <td></td>
                        <td></td>
                    </tr>

                </table>

                <hr>

                <table class="table" style="width: 400px">
                    <tr>
                        <th v-text="title_table">Index</th>
                        <th>Value</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr v-for="(value, id) in params.values">
                        <td><div class="data" v-text="id"></div></td>
                        <td><div class="data" v-text="value"></div></td>
                        <td width="40px">
                            <router-link :to="{ name: 'index', query: $mp({edit: {value: value, key: key, u_key: id, type: params.type} }) }" class="btn btn-link"><i class="glyphicon glyphicon-pencil"></i></router-link>
                        </td>
                        <td width="40px">
                            <button @click="deleteClick(id)"><i class="glyphicon glyphicon-remove-sign"></i></button>
                        </td>
                    </tr>
                </table>

                <p v-if="params.type !== 'string'">
                    <router-link :to="{ name: 'index', query: $mp({edit: {key: params.key, type: params.type, added_list: true} }) }" class="btn btn-link">Add another value</router-link>
                </p>

                <div v-if="params.count == params.max_count && params.type == 'list'">
                    <div class="btn-group">
                        <router-link :to="{ name: 'index', query: {page_list: 1, page: $route.query.page, key: key} }" class="btn btn-default">First</router-link>
                        <button type="button" class="btn btn-default">|</button>
                        <router-link :to="{ name: 'index', query: {page_list: page + 1, page: $route.query.page, key: key} }" class="btn btn-default">Next</router-link>
                    </div>
                </div>
            </div>

        </div>
    </transition>

</template>

<script>
    import ttlEditor from 'components/ttl.vue';
    import titleEditor from 'components/title.vue';
    import Info from './Info.vue';
    export default {
        name: 'editor-component',
        props: {
            removed: ''
        },
        computed: {
            key() {
                return decodeURIComponent(this.$route.query.key || '');
            },
            title_table() {
                if(this.params.type === "string") return 'Index'
                if(this.params.type === "hash") return 'Key'
                if(this.params.type === "list") return 'Index'
                if(this.params.type === "set") return 'Index'
                if(this.params.type === "zset") return 'Score'
            },
            db() {
                return this.$route.query.db || 0;
            },
            added_list_computed() {
                return this.$route.query.added_list || false;
            },
        },

        data: function () {
            return {
                params: {},
                page: 1,
                added_list: false,
                show: false
            }
        },
        watch: {
            key(val) {
                if(val !== "") this.getData(val);
            },
            removed(val) {
                if (this.key === val) this.$router.push({
                    name: 'index',
                    query: {}
                })
            },
            added_list(val) {
                if (val == true) {
                    this.added_list = false;
                    this.getData();
                }
            },
            added_list_computed(val) {
                this.added_list = val
            },
            '$route' (to, from) {

            }
        },
        methods: {
            deleteClick(u_key = false) {
                let params = {key: this.key};
                if (u_key) params['u_key'] = u_key;
                console.log(params, u_key);
                this.$axios.delete("/removeItem", { params: this.$mp_axios(params) })
                    .then(response => {
                        if(!u_key) this.$router.push({name: 'index'})
                        else this.$delete(this.params.values, u_key)
                    })
                    .catch(e => console.log(e))
            },

            getData() {
                this.show = false;
                this.$axios.get("/getItem", { params: this.$mp_axios({ key: this.key, page: this.page}) })
                    .then(response => {this.params = response.data; this.show = true})
                    .catch(e => {console.log(e);this.show = true})
            },
        },
        mounted() {
            if (this.key !== "") this.getData(this.key);
        },
        components: {
            'ttl-editor': ttlEditor,
            'title-editor': titleEditor,
            'info_component': Info
        }
    }
</script>

<style>
    td {
        padding: 10px;
        background-color: #e7e4de;

        border: 1px solid white;
    }
</style>
