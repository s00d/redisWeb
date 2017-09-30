<template>
    <tr>
        <td><abbr title="Time To Live">TTL</abbr>:</td>
        <td>
            <input type="text" class="form-control" :value="value" @input="setValue" v-if="edit">
            <span v-text="(value == -1) ? 'does not expire' : value" v-else></span>

        </td>
        <td><button class="btn btn-info" type="button" @click="set"><i class="glyphicon glyphicon-pencil"></i></button></td>
        <td><button class="btn btn-danger" type="button" @click="remove"><i class="glyphicon glyphicon-remove-sign"></i></button></td>
    </tr>
</template>

<script>
    export default {
        name: 'ttl-editor',
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
                edit: false
            }
        },
        computed: {

        },
        methods: {
            set() {
                if(!this.edit) this.edit = true;
                else {
                    this.edit = false;
                    this.sendRequest(this.value);
                }
            },
            setValue($event) {
                this.$emit('input', $event.target.value)
            },
            remove() {
                this.sendRequest(-1);
            },
            sendRequest(value) {
                this.$axios.post("/setTTL", this.$mp_axios({key: this.key_item, ttl: value}))
                    .then(response => this.$emit('input', value))
                    .catch(e => console.log(e))
            }

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
