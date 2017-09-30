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
        socket:{
            events:{
                connect(){
                    console.log('socket connect...')
                },
                connected(val){
                    console.log('socket connected...', val);
                    this.timestamp = val;
                    this.socketConnected = false;
                    this.$socket.emit('registe', JSON.stringify({redisKey: document.querySelector('#redis-key').getAttribute('value')}));
                },
                reconnecting(val){
                    console.log('socket reconnecting...', val);
                },
                reconnect(val){
                    console.log('socket reconnect...', val);
                },
                error(val) {
                    console.log('error', val)
                },
                connect_error(val) {
                    console.log('connect_error', val)
                },
                connect_timeout(val) {
                    console.log('connect_timeout', val)
                },
                disconnect() {
                    this.socketConnected = false;
                    console.log('socket disconnect...')
                },
                message(val){
                    console.log('msg...', val)
                    if(val.event === 'queue') this.queues.push({channel: val.channel, message: val.message});
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

        },
        mounted() {

        },
        components: {
            'header-component': Header,
            'notifications2': notifications2
        }
    }
</script>
