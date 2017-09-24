<template>
    <div class="starter-template">
        <h1>Redis-web v0.1</h1>

        <b>local server</b>

        <p class="lead">Redis version: <span v-text="data.overview.redis_version"></span></p>
        <p>Keys: <span v-text="data.overview.size"></span></p>
        <p>Memory used: <span v-text="bytesToSize(data.overview.used_memory)"></span></p>
        <p>Uptime: <span v-text="secondsToTime(data.overview.uptime_in_seconds)"></span></p>
        <p>Last save: <span v-text="timeConverter(data.overview.rdb_last_save_time)"></span></p>

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
                    info: {}
                },
                show_info: false
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
