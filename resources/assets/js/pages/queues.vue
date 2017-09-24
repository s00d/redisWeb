<template>
    <div>
        <header-component></header-component>
        <div class="container">

            <div class="col-sm-12 col-md-12 main">
                <ul class="messages">
                    <li class="message" v-for="(value,key) in queues">
                        <span v-text="value.channel"></span>: <span v-text="value.message"></span>
                    </li>
                </ul>
            </div>
            <div id="vanillatoasts-container" >
                <notifications2></notifications2>
            </div>
        </div>
    </div>
</template>

<script>
    import Header from './area/Header.vue'
    import notifications2 from 'components/notifications2.vue'

    export default {
        socket: {
            chanels:{
                'channel.queues.add': function (e) {
                    this.queues.push({channel: e.channel, message: e.message});
                }
            }
        },
        data () {
            return {
                queues: []
            }
        },
        computed: {
            db() {
                return this.$route.query.db || 0;
            },
        },
        watch: {

        },
        methods: {
            getData() {

            },

        },
        mounted() {
            this.getData();
        },
        components: {
            'header-component': Header,
            'notifications2': notifications2
        }
    }
</script>
