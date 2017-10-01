<template>
    <div class="starter-template">


        <div>
            <ul class="nav nav-tabs">
                <li :class="{active: select === 'info'}"><a @click="select = 'info'">Info</a></li>
                <li :class="{active: select === 'terminal'}"><a @click="select = 'terminal'">Terminal</a></li>
            </ul>
        </div>

        <div v-if="select === 'terminal'">
            <div class="form-group">
                <label for="comment">History:</label>
                <textarea class="form-control black" rows="10" id="comment" v-model="commandHistory"></textarea>
            </div>
            <div class="input-group input-group-sm">
                <input type="text" placeholder="Command" class="form-control black" v-model="command">
                <span class="input-group-btn">
                    <a @click="sendCommand()" class="btn btn-default black" >Enter</a>
                </span>
            </div>
        </div>


        <div v-if="select === 'info'">
            <h1>Redis-web v<span v-text="data.overview.server_version"></span></h1>
            <h4 v-text="data.overview.server_description" style="color: #aeaeae"></h4>
            <hr>

            <b>local server</b>

            <p class="lead">Redis version: <span v-text="data.overview.redis_version"></span></p>
            <p>Keys: <span v-text="data.overview.size"></span></p>
            <p>Memory used: <span v-text="bytesToSize(data.overview.used_memory)"></span></p>
            <p>Uptime: <span v-text="secondsToTime(data.overview.uptime_in_seconds)"></span></p>
            <p>Last save: <span v-text="timeConverter(data.overview.rdb_last_save_time)"></span></p>
            <p>User cpu: <span v-text="data.overview.user_cpu"></span></p>
            <p>System cpu: <span v-text="data.overview.system_cpu"></span></p>
            <p>Clients: <span v-text="data.overview.clients"></span></p>

            <p><a href="https://github.com/s00d/redisWeb" target="_blank">Redis-web on GitHub</a></p>
            <p><a href="http://redis.io/documentation" target="_blank">Redis Documentation</a></p>

            <button class="btn btn-info" @click="show_info = !show_info">Show Info</button>
            <div class="info" v-show="show_info">
                <table class="table">
                    <thead>
                    <tr>
                        <th>key</th>
                        <th>value</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(data, key) in data.info">
                        <td><span v-text="key"></span></td>
                        <td>
                            <div class="col-xs-12 col-md-4" v-for="(value, id) in data">
                            <span class="label label-primary">
                                <span v-text="id"></span>: <span v-text="value"></span>
                            </span>
                            </div>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'info-component',
        computed: {

        },
        data: function () {
            return {
                data: {
                    overview: [],
                    info: {},
                },
                show_info: false,
                select: 'info',
                command: '',
                commandHistory: ''
            }
        },
        watch: {
            '$route': function () {
                this.getData()
            }
        },
        methods: {
            getData() {
                this.$axios.get("/getInfo", { params: this.$mp_axios({}) } )
                    .then(response => console.log(this.data = response.data))
                    .catch(e => console.log(e))
            },
             timeConverter(UNIX_timestamp) {
                 let a = new Date(UNIX_timestamp * 1000);
                 let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                 return a.getDate() + ' ' + months[a.getMonth()] + ' ' + a.getFullYear() + ' ' + a.getHours() + ':' + a.getMinutes() + ':' + a.getSeconds();
             },
             bytesToSize(bytes) {
                let sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                if (bytes === 0) return 'n/a';
                 let i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
                if (i === 0) return `${bytes} ${sizes[i]})`;
                return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
            },
            secondsToTime(secs) {
                if(!secs) return 0;
                return (new Date(secs * 1000)).toUTCString().match(/(\d\d:\d\d:\d\d)/)[0];
            },

            sendCommand() {
                this.$axios.post("/saveItem", {command: this.command}).then(response => {
                    this.commandHistory += this.command+ ": "+ response.data.result + "\n";
                }).catch(e => console.log(e))
            }
        },
        mounted() {
            this.getData();
        },
        components: {

        }
    }
</script>

<style>

</style>
