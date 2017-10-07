<template>
    <modal v-model="show_modal">
        <h3 slot="header" v-text="key ? 'Edit' : 'Add'"></h3>
        <span slot="body">
            <div class="input-group input-group-sm">
                <span class="input-group-addon">type: </span>
                <select class="form-control input-sm" v-model="type">
                    <option value="string">String</option>
                    <option value="hash" >Hash</option>
                    <option value="list">List</option>
                    <option value="set">Set</option>
                    <option value="zset">ZSet</option>
                </select>

            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Key: </span>
                <input type="text" class="form-control" v-model="new_key" size="30">
            </div>
            <div class="input-group input-group-sm" v-if="type === 'hash' || type === 'list' || type === 'zset'">
                <span class="input-group-addon" v-text="title_table">Value: </span>
                <input type="text" class="form-control" v-model="u_key" size="30">
            </div>
            <span class="info" v-if="type === 'list'">empty to append, -1 to prepend</span>

            <div class="form-group">
                <label>Value:</label>
                <textarea class="form-control" rows="10" v-model="new_value"></textarea>
            </div>

        </span>

        <span slot="footer">
            <router-link :to="{ name: 'index', query: $mp({edit: false}) }" class="btn btn-info">Cancel</router-link>
            <button type="button" class="btn btn-primary" v-text="key ? 'Edit' : 'Add'" @click="save" :disabled="new_key === ''"></button>
        </span>
    </modal>
</template>

<script>
    import modal from 'components/modal.vue'
    export default {
        data () {
            return {
                show_modal: false,
                type: 'string',
                new_value: '',
                old_value: '',
                new_key: '',
                old_key: '',
                u_key: '',
                added_list: false
            }
        },
        computed: {
            key() {
                return this.$route.query.edit_key || false
            },
            edit() {
                return this.$route.query.edit || false
            },
            db() {
                return this.$route.query.db || 0
            },
            title_table() {
                if(this.type === "hash") return 'Hash key:';
                if(this.type === "list") return 'Index:';
                if(this.type === "zset") return 'Score:'
            }
        },
        watch: {
            edit(val) {
                this.show_modal = this.edit === 'false' || this.edit === false ? false : true;
                console.log(this.edit, this.show_modal);
                if(val instanceof Object) {
                    this.new_value = ('value' in val) ? val.value : '';
                    this.old_value = ('value' in val) ? val.value : '';
                    this.new_key = ('key' in val) ? val.key : '';
                    this.old_key = ('key' in val) ? val.key : '';
                    this.u_key = ('u_key' in val) ? val.u_key : '';
                    this.type = ('type' in val) ? val.type : 'string';
                    this.added_list = ('added_list' in val) ? val.added_list : false;
                }

            }
        },
        methods: {
            save() {
//                this.$store.commit('tree/rename', {old_link: this.old_key, new_link:this.new_key});
                this.$axios.post("/saveItem", {
                    new_value: this.new_value,
                    old_value: this.old_value,
                    new_key: this.new_key,
                    old_key: this.old_key,
                    u_key: this.u_key,
                    type: this.type,
                    db: this.db
                }).then(response => {
                    this.$router.push({
                        name: 'index',
                        query: this.$mp({edit: false, key: this.new_key, added_list: this.added_list})
                    });
                    if(this.old_value === '') this.$store.commit('tree/pushNew', this.new_key);
                    else if (this.old_value !== this.new_value) this.$store.commit('tree/rename', {old_link: this.old_key, new_link:this.new_key});
                    this.$notify('saved');
                }).catch(e => console.log(e))
            }
        },
        mounted() {
            this.show_modal = this.edit === 'false' || this.edit === false ? false : true;
        },
        components: {
            modal
        }
    }
</script>
