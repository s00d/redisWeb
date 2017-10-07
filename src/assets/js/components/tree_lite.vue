<template>
    <li v-if="show">
        <div :class="{bold: isFolder, selected: open}" @click="toggle">
            <span >
                <span v-if="isFolder"> [{{open ? '-' : '+'}}] </span>
                <i class="glyphicon glyphicon-folder-open" v-if="isFolder"></i> {{name}}
            </span>
            <a class="btn pull-right btn-xs glyphicon glyphicon-remove" :style="{color: isFolder ? '#3097D1' : 'red'}" @click.stop="remove()"></a>
        </div>
        <ul v-show="open" v-if="isFolder">
            <tree-component
                class="item"
                v-for="(model, key) in filteredTree"
                :filter="filter"
                :key="key"
                :name="key"
                :link="sendLink"
                :tree="model"
                @delete="removeByKey">
            </tree-component>
        </ul>
    </li>
</template>

<script>
    import _ from 'lodash';
    export default {
        name: 'tree-component',
        props: {
            name: '',
            key: '',
            tree: {
                type: [Number, String, Object, Array]
            },
            link: {
                type: String,
                default: ""
            },
            filter: ''
        },
        data: function () {
            return {
                open: false,
                show: true
            }
        },
        computed: {
            sendLink() {
                let link = (this.link !== "") ? this.link +":" : "";
                return link + this.name
            },
            isFolder() {
                return this.tree !== 'key'
            },
            filteredTree() {
                let filter = this.filter.replace('*','').toLowerCase();
                if (filter === '') return this.tree;
                if (!this.isFolder && this.tree.toLowerCase().indexOf(filter) > -1) return this.show = false;
                return _.omitBy(this.tree, (o, key) =>  key.toLowerCase().indexOf(filter) === -1 );
            }
        },
        methods: {
            toggle() {
                if (this.isFolder) {
                    this.open = !this.open
                } else {
                    let query = this.$mp({key: this.sendLink});
                    this.$router.push({
                        name: 'index',
                        query: this.$mp({key: this.sendLink})
                    });

                }
            },
            removeByKey(key) {
                this.$delete(this.tree, key)
            },
            remove(type) {
                this.$axios.delete("/removeItem",{ params: this.$mp_axios({ key: this.sendLink, type: this.isFolder ? 'tree' : 'string'}) })
                    .then(response => {
                        this.$emit('delete', key)
                    })
                    .catch(e => console.log(e))
            }
        }
    }
</script>

<style scoped>
    li div {
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border: 1px solid #999;
        border-radius: 5px;
        padding: 3px 8px;
        text-decoration: none;
        cursor: pointer;
    }

    ul {
        list-style-type: none;
        padding-left: 10px;
    }

    li div:hover {
        background-color: #d8dfe5;
    }

    li div.selected {
        background-color: #d8dfe5;
    }
</style>
