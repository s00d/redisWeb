<template>
    <div id="app" class="layout-boxed">
        <transition appear :name="transitionName" mode="out-in">
            <keep-alive>
                <router-view class="view"></router-view>
            </keep-alive>
        </transition>
    </div>

</template>
<script>
    import Vue from 'vue'
    export default {
        name: 'App',
        data () {
            return {
                load: true,
                transitionName: 'slide-up'
            };
        },
        watch: {
            '$route' (to, from) {
                const toDepth = to.path.split('/').length;
                const fromDepth = from.path.split('/').length;
                if (toDepth === fromDepth) {
                    this.transitionName = 'slide-up'
                } else {
                    this.transitionName = toDepth < fromDepth ? 'slide-right' : 'slide-left'
                }
            }
        },
        methods: {
            handleResize () {

            },

        },
        beforeCreate: function () {

        },
        mounted () {

        },

        components:{

        }

    };
</script>
<style lang="css">

    .container {
        margin-left: 10px;
    }

    .no-padding {
        padding: 0 !important;
    }

    .black {
        background-color: #2a3f53;
        color: white;
    }
</style>