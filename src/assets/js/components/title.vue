<template>
    <tr>
        <td>Name:</td>
        <td>
            <input type="text" class="form-control" v-model="value_fuild" v-if="edit">
            <span v-text="value" v-else></span>
        </td>
        <td><button @click="set" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></button></td>
        <td></td>
    </tr>
</template>

<script>
    export default {
        name: 'title-editor',
        props: {
            key_item: {
                type: String,
                default: ""
            },
            value: {
                type: [Number, String],
                default: -1
            },
        },
        data: function () {
            return {
                open: false,
                edit: false,
                value_fuild: ''
            }
        },
        watch: {
            value(val) {
                this.value_fuild = val
            }
        },
        methods: {
            set() {
                if(!this.edit) this.edit = true;
                else {
                    this.edit = false;
                    this.sendRequest();
                }
            },
            sendRequest() {
                this.$axios.post("/setName", this.$mp_axios({key: this.key_item, new_name: this.value_fuild, old_name: this.value}) )
                    .then(response => {
                        this.$store.commit('tree/rename', {old_link: this.value, new_link:this.value_fuild});
                        this.$router.push({
                            name: 'index',
                            query: this.$mp({key: response.data.result})
                        })
                    })
                    .catch(e => console.log(e))
            }

        },
        mounted() {
            this.value_fuild = this.value
        }
    }
</script>

<style scoped>
    li div {
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border: 1px solid #999;
        border-radius: 5px;
        padding: 3px 8px;
        text-decoration: none;
    }
</style>
