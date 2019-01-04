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

            <slot></slot>
        </el-card>
    </div>
</template>
<script>
    export default {

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            text: String,
            author: String,
        }

    }
</script>
