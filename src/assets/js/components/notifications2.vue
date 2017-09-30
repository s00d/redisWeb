<template>
    <div>
        <div v-for="(notification, id) in notificatoins" :id="idFilter(id)" class="vanillatoasts-toast vanillatoasts-info" @click="click(notification)">
        <table style="width: 100%;">
            <tbody>
            <tr>
                <td v-if="notification.type" style="width: 10%; height: 50px;" rowspan="2"><img :src="iconFilter(notification.type)" class="vanillatoasts-icon"></td>
                <td v-if="notification.title"  style="height: 25px;">
                    <h4 class="vanillatoasts-title" v-text="notification.title"></h4>
                    <button type="button" class="btn btn-link btn-xs hide-button" @click="remove(notification)"><i class="glyphicon glyphicon-remove"></i></button>
                </td>
            </tr>
            <tr>
                <td style=" height: 25px;"><p class="vanillatoasts-text" v-html="notification.text"></p></td>
                </tr>
            </tbody>
            </table>
        </div>

    </div>
</template>
<style lang="css">
    .hide-button {
        position: absolute;
        right: 4px;
        top: 4px;
    }
</style>
<script> /** <script type='text/babel' lang="babel"> */
    import Vue from 'vue';
    import { mapState } from 'vuex'

    export default{
        name: "notifications",
        computed: {
            ...mapState({
                notificatoins: state => state.notifications.data
            })
        },
        data () {
            return {
                disableRequestPermission: false
            };
        },
        filters: {

        },
        methods: {
            iconFilter: (type) => '/images/pnotify/'+type+'.png',
            idFilter: (id) => 'notify-'+id,
            remove(notification) {
                this.$store.commit('notifications/remove', notification)
            },
            click(notification) {
                if (notification.callback) notification.callback();
                this.$store.commit('notifications/remove', notification)
            },

            localNotification(notification) {
                this.$store.commit('notifications/push', notification)
                if (typeof notification.timeout === 'number' && notification.timeout > 0) {
                    setTimeout(() => this.$store.commit('notifications/remove', notification), notification.timeout)
                }
            },
            globalNotification(notification) {
                try {
                    let ntf = new Notification(notification.title, {
                        icon: '/images/pnotify/'+notification.type+'.png',
                        body: notification.text,
                        tag : notification.type
                    });
                    ntf.onClick = notification.callback ? notification.callback : () => window.focus();
                    ntf.onError = () => this.localNotification(notification);
                } catch (err) {
                    this.localNotification(notification);
                }
            },

            permission() {
                try {
                    if (location.protocol !== 'https:') return false;
                } catch (err) { }
                try {
                    if (this.disableRequestPermission) return false;
                    if (!("Notification" in window)) return false;
                    if (Notification.permission === "granted") return true;
                    if (Notification.permission !== 'denied' || Notification.permission === "default") {
                        Notification.requestPermission((permission) => (permission === "granted") );
                    }
                } catch (err) {
                    this. disableRequestPermission = true;
                }
                return false;
            },
            addNotification(notification = {timeout: 3000}) {
                if(!notification.title)    notification.title = '';
                if(!notification.text)     notification.text = '';
                if(!notification.type)     notification.type = '';
                if(!notification.callback) notification.callback = false;
                if(!notification.id)       notification.id = new Date().getTime();
                if(this.permission()) this.globalNotification(notification);
                else this.localNotification(notification);
            }

        },
        created() {
            Vue.ntf = {};
            Vue.ntf.notify = Vue.prototype.$notify = (text, options = {id: false, title:'notify', type: 'info', timeout: 10000, callback: false}) => {
                this.addNotification({title: options.title, text: text, ...options })
            };
            Vue.ntf.error = Vue.prototype.$error = (text, options = {id: false, timeout: 10000, callback: false}) => {
                this.addNotification({title: "Error", text: text, type: "warning", ...options })
            };
            Vue.ntf.warn = Vue.prototype.$warn = (text, options = {id: false, timeout: 10000, callback: false}) => {
                this.addNotification({title: "Warning", text: text, type: "danger", ...options })
            };
            Vue.ntf.success = Vue.prototype.$success = (text, options = {id: false, timeout: 10000, callback: false}) => {
                this.addNotification({title: "Success", text: text, type: "success", ...options })
            };
        }

    }
</script>
