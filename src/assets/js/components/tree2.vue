<template>
    <li v-if="show" class="tree_item">
        <div :class="{bold: isFolder, selected: tree.open || selected}" @click="toggle" :id="id" draggable='true' @dragstart='dragStart' @dragover='dragOver' @dragenter='dragEnter' @dragleave='dragLeave' @drop='drop($event, id)' @dragend='dragEnd'>
            <span>
                <span v-if="isFolder"> [{{tree.open ? '-' : '+'}}] </span>
                <i class="glyphicon glyphicon-folder-open" v-if="isFolder"></i>
                <span v-if="!tree.edit" v-text="tree.name"></span>
                <input v-else type="text" style="width: 80%" class="black" v-model="link" @keyup.enter="$store.commit('tree/edit', tree.link)">
            </span>

            <a class="btn pull-right btn-xs glyphicon glyphicon-remove"
               v-if="tree.name !== 'root'"
               :class="isFolder ? 'remove-folder' : 'remove-item'" @click.stop="remove(tree.link)">
            </a>

            <a class="btn pull-right btn-xs glyphicon glyphicon-pencil"
               v-if="!isFolder"
               @click.stop="$store.commit('tree/edit', tree.link)">
            </a>


            <span class="label label-default pull-right mr-fix" v-text="length" v-if="isFolder"></span>
            <span class="fa fa-spinner fa-spin pull-right"
                  style="margin-right: 10px;margin-top: 4px;"
                  v-if="tree.open && tree.children === 'unload'">
            </span>
        </div>

        <ul v-if="isFolder && tree.open" class="tree">
            <tree2-component v-if="!isFolderLoad && index >= (page*maxInPage) && index<(page+1)*maxInPage" v-for="(data, key, index) in tree.children"
                            class="item"
                            :id="key"
                            :key="data.link"
                            :tree="data">
            </tree2-component>

            <li v-if="length > maxInPage">
                <div style="text-align: center">
                    <button class="btn btn-default" :disabled="page === 0" @click="page--">First</button>
                    <span type="button" class="btn btn-default"
                          v-for="in_page in lastPage"
                          v-text="in_page"
                          :disabled="page === in_page-1"
                          @click="page = in_page-1"
                    ></span>
                    <button class="btn btn-default" :disabled="page === lastPage" @click="page++">Next</button>
                    <input type="number" class="form-control" v-model="maxInPage" style="display: inline-block; width: 50px; padding: 6px 2px">
                </div>
            </li>
            <!--<li class='add_tree'><div>+</div></li>-->
        </ul>
    </li>


</template>

<script>
    import { mapGetters, mapState } from 'vuex'
    import { omitBy } from 'lodash';

    let toData = '';

    export default {
        name: 'tree2-component',
        props: {
            id: {
                type: [Number, String]
            },
            tree: {
                type: [Number, String, Object, Array]
            }
        },
        data: function () {
            return {
                show: true,
                page:0,
                maxInPage: 100

                // drag

            }
        },
        computed: {
            ...mapState({
                save: state => state.settings.data.save
            }),
            isFolder() {
                return 'children' in this.tree && this.tree.children !== false;  //&& Object.keys(this.tree.children).length;
            },
            isFolderLoad() {
                return this.isFolder && this.tree.children === 'unload';
            },
            selected() {
                return this.$route.query.key === this.tree.link
            },
            length() {
                return 'children' in this.tree && this.tree.children !== 'unload' ? Object.keys(this.tree.children).length : 'un'
            },
            lastPage() {
                return Math.ceil(this.length/this.maxInPage)-1;
            },
            link: {
                get() {
                    return this.tree.link;
                },
                set(val) {
                    return  this.$store.commit('tree/rename', {old_link: this.tree.link, new_link: val});
                }
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
                e.target.style.background = '#345cff';
                e.preventDefault()
                return true
            },
            drop(e, id) {
                this.$store.commit('tree/dropItems', {link: this.tree.link, fromKey: id, toKey: toData});

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
