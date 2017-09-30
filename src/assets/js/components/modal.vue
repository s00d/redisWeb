<template>
    <transition name="modal">
        <div v-if="show">
            <div class="modal fade in" role="dialog" style="display: block; padding-left: 0px;">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button v-if="close" type="button" class="btn btn-link close-model" @click="clickShow()" >
                                <i class="glyphicon glyphicon-remove"></i>
                            </button>
                            <slot name="header">default header</slot>
                        </div>
                        <div class="modal-body">
                            <slot name="body">default body</slot>
                        </div>
                        <div class="modal-footer">
                            <slot name="footer">
                                <button type="button" class="btn btn-primary" @click="clickShow()">OK</button>
                            </slot>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-backdrop fade in"></div>
        </div>
    </transition>
</template>

<script> /** <script type='text/babel' lang="babel"> */
export default{
    name: "modal",
    props: {
        value: {
            type: Boolean,
            default: false
        },
        width: {
            type: Number,
            twoWay: 800
        },
        close: {
            type: Boolean,
            default: false
        },
        name: {
            type: String,
            default: 'model'
        },
    },
    data () {
        return {
            show: false,
        };
    },
    watch: {
        value(val) {
            this.show = val
        }
    },
    methods: {
        clickShow(show = !this.show) {
            this.$emit('input', show);
        }
    },
    created () {

    },
    beforeDestroy () {

    },
}
</script>

<style lang="css">
    .close-model{
        position: absolute;
        right: 10px;
        color: #d9534f;
    }

    .modal-header h3 {
        margin-top: 0;
    }

    .modal-body {
        margin: 20px 0;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    .modal {
        text-align: center;
        padding: 0 !important;

        overflow-x: hidden;
        overflow-y: auto !important;
    }

    .modal:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    h3 {
        margin-bottom: 0;
    }
</style>

