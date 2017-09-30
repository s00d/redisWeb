<template>
    <header>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <router-link class="navbar-brand" :to="{ name: 'index', query: {} }">Redis WEB</router-link>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <!--<li><router-link :to="{ name: '≈', query: {} }">Editor</router-link></li>-->

                        <li><router-link :to="{ name: 'index',  query: {} }">Home/About</router-link></li>
                        <li><router-link :to="{ name: 'queues', query: {} }">Queues</router-link></li>

                        <li><a @click="show_import = true">Import</a></li>
                        <li><a target="_blank" :href="'/api/export?db=' + db">Export</a></li>
                        <li><a target="_blank" :href="'/api/export?db=' + db +'&type=json'">Export JSON</a></li>

                        <!--<li><a href="#" @click="show_modal = !show_modal">Settings</a></li>-->
                        <li>
                            <a href="#"
                               @click="$store.commit('settings/set', {save: !save})"
                               :style="{'background-color': save ? '#545454' : 'transparent'}">
                            Save state
                            </a>
                        </li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>

        <modal v-model="show_modal" :close="true">
            <h3 slot="header">Settings</h3>
            <span slot="body">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Filter">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button">Filter</button>
                    </span>
                </div>
            </span>
        </modal>

        <modal v-model="show_import" :close="true">
            <h3 slot="header">Import</h3>
            <span slot="body">
                <div class="form-group" style="height: 180px;">
                    <label class="control-label col-sm-2" >Commands:<br><br>
                        <span class="info">Valid are:<br>SET<br>HSET<br>LPUSH<br>RPUSH<br>LSET<br>SADD<br>ZADD</span>
                    </label>
                    <div class="col-sm-10">
                        <textarea rows="10" class="form-control" v-model="import_text"></textarea>
                    </div>
                </div>
                <hr>
            </span>
            <span slot="footer">
                <button type="button" class="btn btn-default" @click="importData">Import</button>
            </span>
        </modal>
    </header>
</template>

<script>
    import modal from 'components/modal.vue'
    import { mapGetters, mapState } from 'vuex'

    export default {
        data () {
            return {
                show_modal: false,
                show_import: false,
                import_text: '',

            }
        },
        computed: {
            ...mapState({
                save: state => state.settings.data.save,
            }),
            db() {
                return this.$route.query.db || 0;
            },
        },
        methods: {
            importData() {
                this.$axios.post("/import", this.$mp_axios({import_text: this.import_text})).then(response => {
                    this.$router.push({
                        name: 'index',
                        query: this.$mp({edit: false, key: this.new_key})
                    });
                    this.$emit('reload');
                    this.$notify('imported');
                }).catch(e => console.log(e))
            }
        },
        mounted() {

        },
        components: {
            modal
        }
    }
</script>

<style scoped>
    .info {
        color: #aaa;
        font-weight: normal;
    }

    .router-link-exact-active {
        color: white !important;
    }
</style>