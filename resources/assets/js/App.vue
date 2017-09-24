<template>
    <div id="app" class="layout-boxed">


        <transition mode="out-in">
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

            };
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

</style>