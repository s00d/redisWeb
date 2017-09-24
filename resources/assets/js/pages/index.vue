<template>
    <div>
        <header-component @reload="getData()"></header-component>
        <div class="container">
            <div class="col-sm-3 col-md-2 sidebar">
                <menu-component :params="params"></menu-component>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-1 main">
                <editor-component :removed="removed"></editor-component>
            </div>
            <new-or-edit-component></new-or-edit-component>
            <div id="vanillatoasts-container" >
                <notifications2></notifications2>
            </div>
        </div>
    </div>
</template>

<script>
    import Header from './area/Header.vue'
    import Menu from './area/Menu.vue'
    import Editor from './area/Editor.vue'
    import NewOrEdit from './area/NewOrEdit.vue';
    import notifications2 from 'components/notifications2.vue'
    import { mapGetters, mapState } from 'vuex'

    export default {
        data () {
            return {
                params: {},
                removed: ''
            }
        },
        computed: {
            page() {
                return this.$route.query.page || 0;
            },
            all() {
                return this.$route.query.all || false;
            },
            db() {
                return this.$route.query.db || 0;
            },
            ...mapState({
                filter: state => state.tree.filter
            }),
            server() {
                return this.$route.query.server || 'default';
            },
            added_list() {
                return this.$route.query.added_list || false;
            }
        },
        watch: {
            db(val) {
                this.getData()
            },
            page(val) {
                this.getData()
            },
            server(val) {
                this.getData()
            }
        },
        methods: {
            getData(page = 0) {
                this.$axios.get("/getList", { params: this.$mp_axios({ page: this.page, all: this.all}) })
                    .then(response => {
                        this.params = response.data.params;
                        this.$store.commit('tree/set', response.data.tree)
                    })
                    .catch(e => console.log(e))
            }
        },
        mounted() {
            this.getData();
        },
        components: {
            'header-component': Header,
            'menu-component': Menu,
            'editor-component': Editor,
            'new-or-edit-component' : NewOrEdit,
            'notifications2': notifications2
        }
    }
</script>
