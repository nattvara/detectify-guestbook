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
    }

    p {
        margin: 0px 0px 20px 0px;
    }

    .votes {
        margin: 0px 0px 5px 0px;

        .el-badge {
            margin-right: 30px;

            .vote {
                padding: 10px 10px 10px 0px;
            }
        }
    }

</style>

<template>
    <div>
        <el-card class="message" shadow="hover">
            <el-row>
                <h4><em>{{ author }}</em> writes,</h4>
            </el-row>
            <el-row>
                <p>{{ text }}</p>
            </el-row>

            <el-row class="votes">
                <el-badge :value="12" class="item">
                    <el-button type="text" class="vote">Upvote</el-button>
                </el-badge>
                <el-badge :value="12" class="item">
                    <el-button type="text" class="vote">Downvote</el-button>
                </el-badge>
            </el-row>

            <div>
                <message
                    v-for="message in responses(messages, parentId)"
                    v-if="message.parent_id === parentId"
                    :key="message.id"
                    :id="message.id"
                    :parent-id="message.id"
                    :author="message.author"
                    :author-id="message.author_id"
                    :text="message.text"
                    :created-at="message.created_at"
                    :responses="responses"
                    :messages="messages"
                    ></message>
            </div>
        </el-card>
    </div>
</template>

<script>
    export default {

        name: 'message',

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            id: String,
            text: String,
            author: String,
            authorId: String,
            parentId: String,
            createdAt: String,
            messages: Array,
            responses: Function
        }

    }
</script>
