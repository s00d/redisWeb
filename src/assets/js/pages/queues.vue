<template>
    <div>
        <header-component></header-component>
        <div class="container">
            <div class="col-sm-3 col-md-2 sidebar black">
                <li v-for="(val, name) in channels"
                    v-text="name"
                    :id="name"
                    @click="select_channel = name" style="cursor: pointer">
                </li>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-3 main">
                <div>
                    <ul class="nav nav-tabs">
                        <li :class="{active: select === 'info'}"><a @click="select = 'queues'">Queues</a></li>
                        <li :class="{active: select === 'monitor'}"><a @click="select = 'monitor'">Monitor</a></li>
                    </ul>
                </div>

                <ul class="messages" v-if="select === 'queues' && select_channel">
                    <li class="message" v-for="(value,key) in channels[select_channel]">
                        <span v-text="value"></span>
                    </li>
                </ul>
                <ul class="messages" v-if="select === 'monitor'">
                    <li class="message" v-for="(data,key) in monitor">
                        <span v-text="data.time"></span>
                        (<span v-text="data.source"></span> <span v-text="data.database"></span>):
                        <span v-text="data.args"></span>
                    </li>
                </ul>
            </div>

            <!--<div class="col-sm-12 col-md-12 main">-->
                <!--<ul class="messages">-->
                    <!--<li class="message" v-for="(value,key) in queues">-->
                        <!--<span v-text="value.channel"></span>: <span v-text="value.message"></span>-->
                    <!--</li>-->
                <!--</ul>-->
            <!--</div>-->
            <div id="vanillatoasts-container" >
                <notifications2></notifications2>
            </div>
        </div>
    </div>
</template>

<script>
    import Header from './area/Header.vue'
    import notifications2 from 'components/notifications2.vue'
    import Vue from 'vue'

    export default {
        socket:{
            events:{
                connect(){
                    console.log('socket connect...')
                },
                connected(val){
                    console.log('socket connected...', val);
                    this.timestamp = val;
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
                    console.log('socket disconnect...')
                },
                message(val){
                    console.log('msg...', val)
                    if(val.event === 'queue') {
                        if(!(val.channel in this.channels)) Vue.set(this.channels, val.channel, [val.message])
                        else this.channels[val.channel].push(val.message);
                    }
                    if(val.event === 'monitor') this.monitor.push(val.data)
                }
            }

        },
        data () {
            return {
                queues: [],
                monitor: [],
                channels: {},
                select_channel: false,
                select: 'queues',
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
