<template>
    <div>
        <div style="width: 100%" class="text-center">
            <div class="row">
                <div class="col-sm-12 col-md-6" style="padding-right: 1px">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon black">Server: </span>
                        <select class="form-control input-sm black" v-model="server">
                            <option v-for="(server, name) in params.servers" v-text="name +': '+server" :value="name"></option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6" style="padding-left: 1px">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon black">DB: </span>
                        <select class="form-control input-sm black" v-model="db">
                            <option v-for="key in 12" v-text="key-1"></option>
                        </select>
                    </div>
                </div>
            </div>


            <hr>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control black" placeholder="Filter" v-model="filter">
                <span class="form-control-clear glyphicon glyphicon-remove form-control-feedback" v-if="filter !== '' && filter !== '*'" @click="filter = '*'"></span>
                <span class="input-group-btn">
                    <router-link :to="{ name: 'index', query: $mp({filter: filter}) }" class="btn btn-default black">
                        <i class="glyphicon glyphicon-search"></i>
                    </router-link>
                </span>
            </div>

            <router-link :to="{ name: 'index', query: $mp({edit: true}) }" class="btn btn-link black">+ Add another key</router-link>
        </div>
        <ul class="list-group tree no-padding">
            <tree :tree="treeFiltred"></tree>
        </ul>


        <!--<div style="text-align: center" v-if="!params.show_all_item">-->
            <!--<div class="btn-group">-->
                <!--<router-link :to="{ name: 'index', query: $mp({page: 0}) }" class="btn btn-default">First</router-link>-->
                <!--<button type="button" class="btn btn-default">|</button>-->
                <!--<router-link :to="{ name: 'index', query: $mp({page: params.next}) }" class="btn btn-default">Next</router-link>-->
            <!--</div>-->
        <!--</div>-->
    </div>
</template>

<script>
    import tree from 'components/tree2.vue';
    import { omitBy } from 'lodash';
    import { mapGetters, mapState } from 'vuex'

    export default {
        name: 'menu-component',
        props: {
            params: Object
        },
        data () {
            return {
                db: 0,
                server: this.$route.query.server || 'default',
                filter: this.$route.query.filter || '*'
            }
        },
        watch: {
            db(val) {
                this.$router.push({
                    name: 'index',
                    query: this.$mp({db: val})
                })
            },
            route_db(val) {
                this.db = val
            },
            server(val) {
                this.$router.push({name: 'index', query: this.$mp({server: val})});
            },
            filter(val) {
//                this.$store.commit('tree/setFilter', val)
            }
        },
        computed: {
            ...mapGetters({
                treeFiltred: 'tree/getFiltred'
            }),
            route_db() {
                return this.$route.query.db || 0
            },
        },
        methods: {

        },
        mounted() {
            this.db = this.route_db
        },
        components: {
            tree
        }
    }
</script>

<style>
    .btn-group-xs {
        padding-top: 2px;
        padding-bottom: 2px;
    }

    ul.tree {
        list-style-type: none;
        padding-left: 10px;
    }

    li.tree_item div {
        /*-moz-border-radius: 5px;*/
        /*-webkit-border-radius: 5px;*/
        border: 1px solid white;
        /*border-radius: 5px;*/
        padding: 3px 8px;
        text-decoration: none;
        cursor: pointer;
    }

    li.add_tree div {
        border-radius: 5px;
        padding: 3px 8px;
        text-decoration: none;
        cursor: pointer;
        font-weight: bold;
        border: 0;
    }

    li.tree_item div:hover, li.add_tree div:hover {
        background-color: #021321;
        color: white;
    }

    li.tree_item div.selected, li.add_tree div.selected {
        background-color: #546879;
        color: white;
    }

    .form-control-clear {
        z-index: 10;
        pointer-events: auto;
        cursor: pointer;
        right: 30px;
    }
</style>
