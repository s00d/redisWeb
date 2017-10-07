<template>
    <li v-if="show" class="tree_item"  >
        <div :class="{bold: isFolder, selected: tree.open || selected}" @click="toggle" :id="id" draggable='true' @dragstart='dragStart' @dragover='dragOver' @dragenter='dragEnter' @dragleave='dragLeave' @drop='drop' @dragend='dragEnd'>
            <span >
                <span v-if="isFolder"> [{{tree.open ? '-' : '+'}}] </span>
                <i class="glyphicon glyphicon-folder-open" v-if="isFolder"></i>
                <span v-text="tree.name"></span>
            </span>

            <a class="btn pull-right btn-xs glyphicon glyphicon-remove" :class="isFolder ? 'remove-folder' : 'remove-item'" @click.stop="remove(tree.link)"></a>
            <span class="label label-default pull-right mr-fix" v-text="length" v-if="isFolder"></span>
        </div>
        <ul v-if="isFolder && tree.open" class="tree">
            <tree-component v-for="(data, key) in tree.children"
                            class="item"
                            :id="key"
                            :filter="filter"
                            :key="data.link"
                            :tree="data">
            </tree-component>
            <!--<li class='add_tree'><div>+</div></li>-->
        </ul>
    </li>
</template>

<script>
    import { mapGetters, mapState } from 'vuex'
    import { omitBy } from 'lodash';

    let toData = '';

    export default {
        name: 'tree-component',
        props: {
            id: {
                type: [Number, String]
            },
            tree: {
                type: [Number, String, Object, Array]
            },
            filter: ''
        },
        data: function () {
            return {
                show: true,

                // drag

            }
        },
        computed: {
            ...mapState({
                save: state => state.settings.data.save
            }),
            isFolder() {
                return 'children' in this.tree //&& Object.keys(this.tree.children).length;
            },
            selected() {
                return this.$route.query.key === this.tree.link
            },
            length() {
                return 'children' in this.tree ? Object.keys(this.tree.children).length : 0
            }
        },
        methods: {
            toggle() {
                if (this.isFolder) {
                    this.$store.commit('tree/open', this.tree.link);
                } else {
                    this.$router.push({
                        name: 'index',
                        query: this.$mp({key: this.tree.link})
                    });

                }
            },
            addChild() {
                this.tree.children.push({
                    name: this.defaultText ? this.defaultText : 'New Node',
                    id: id++
                })
            },
            remove(link) {
                this.$axios.delete("/removeItem",{ params: this.$mp_axios({ key: link, type: this.isFolder ? 'tree' : 'string'}) })
                    .then(response => {
                        this.$store.commit('tree/del', this.tree.link)
                    })
                    .catch(e => console.log(e))
            },



            // drag
            dragStart(e) {
                // fromData = this.model
                e.dataTransfer.effectAllowed = "move";
                e.dataTransfer.setData("nottext", e.target.innerHTML);
                toData = e.target.id
                e.target.style.background = '#c3c0f8'
                return true
            },
            drag(e) {},
            dragEnter(e) {},
            dragLeave(e) {
                e.target.style.background = '#2a3f53'
            },
            dragEnd(e) {},
            dragOver(e) {
                e.target.style.background = '#345cff'
                e.preventDefault()
                return true
            },
            drop(e) {
                this.$store.commit('tree/dropItems', {link: this.tree.link, fromKey: e.target.id, toKey: toData})

                e.target.style.background = '#101075'
            },
        },
        mounted() {
//            if (this.save) this.open = this.$liteStorage.get(this.tree.link, false);
        }
    }
</script>

<style scoped>
    .mr-fix{
        margin-top: 3px;
        background-color: transparent;
        border: 1px solid white;
    }

    .remove-folder {
        color: #3097D1;
    }
    .remove-item {
        color: red;
    }
    .remove-folder:hover, .remove-item:hover {
        color:white;
    }
</style>
