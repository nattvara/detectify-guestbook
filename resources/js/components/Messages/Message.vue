<style lang="scss" scoped>
    @import '~@/variables';

    .el-card {
        margin: 10px 10px 0px 10px;
        padding-top: 0;
        background: rgba($secondary, .3);
        border-top: none;
        border-right: none;
        border-left: 6px solid mix($secondary, $primary, 70%);
        border-bottom: 6px solid mix($secondary, $primary, 70%);
        box-shadow: 0px 0px 1px mix(mix($secondary, $primary, 70%), #000, 80%);
    }

    p {
        margin: 0px 0px 20px 0px;
    }

    .actions {
        margin: 0px 0px 5px 0px;

        .action {
            float: left;
        }

        *.el-button {
            padding: 10px 10px 10px 0px;
        }

        .el-badge {
            margin-right: 30px;

            &.voted {
                .el-button {
                    color: $success;
                }
            }
        }
    }

</style>

<template>
    <div>
        <el-card class="message" shadow="hover">
            <el-row>
                <h4>
                    <em>{{ author }}</em>
                    <span v-if="isRoot">writes,</span>
                    <span v-if="!isRoot">responds,</span>
                </h4>
            </el-row>
            <el-row>
                <p>{{ text }}</p>
            </el-row>

            <el-row class="actions">
                <el-badge :value="votes.up" class="action" :class="{voted: votes.my_vote === 'up', success: votes.my_vote === 'up'}">
                    <el-button type="text" @click="vote('up');">Upvote</el-button>
                </el-badge>
                <el-badge :value="votes.down" class="action" :class="{voted: votes.my_vote === 'down', success: votes.my_vote === 'down'}">
                    <el-button type="text" @click="vote('down');">Downvote</el-button>
                </el-badge>
                <el-button type="text" class="action" @click="showReply();">
                    Reply
                    <font-awesome-icon icon="reply" />
                </el-button>
            </el-row>

            <div>
                <message
                    v-for="message in responses(messages, parentId)"
                    v-if="message.parent_id === parentId"
                    :key="message.id"
                    :id="message.id"
                    :is-root="false"
                    :parent-id="message.id"
                    :author="message.author"
                    :author-id="message.author_id"
                    :text="message.text"
                    :votes="message.votes"
                    :created-at="message.created_at"
                    :responses="responses"
                    :messages="messages"
                    @reload-messages="$emit('reload-messages');"
                    ></message>
            </div>

            <el-row v-show="replyForm.show" ref="reply">
                <new-message
                    :reply-to="this.id"
                    :show-cancel="true"
                    @cancel="replyForm.show = false;"></new-message>
            </el-row>
        </el-card>
    </div>
</template>

<script>
    import NewMessage from './NewMessage'
    export default {

        /**
         * Component's name
         *
         * @type {String}
         */
        name: 'message',

        /**
         * Components.
         *
         * @type {Object}
         */
        components: {
            NewMessage
        },

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            id: String,
            text: String,
            votes: Object,
            author: String,
            isRoot: Boolean,
            messages: Array,
            authorId: String,
            parentId: String,
            createdAt: String,
            responses: Function
        },

        /**
         * Components data
         *
         * @return {Object}
         */
        data() {
            return {
                replyForm: {
                    show: false,
                }
            };
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Show reply form
             *
             * @return {void}
             */
            async showReply() {
                this.replyForm.show = true;
                await this.sleep(50);
                this.$scrollTo(this.$refs.reply.$el, 300, {
                    force: true,
                    offset: -200
                });
            },

            /**
             * Vote on message
             *
             * @param  {String} sentiment up|down
             * @return {void}
             */
            async vote(sentiment) {
                try {
                    let url             = '/messages/' + this.id + '/vote/' + sentiment;
                    var response        = await axios.post(url);
                    this.votes.up       = response.data.votes.up;
                    this.votes.down     = response.data.votes.down;
                    this.votes.my_vote  = response.data.votes.my_vote;
                } catch (e) {
                    this.alertError(e.response.data.message);
                }
            }
        }
    }
</script>
