<style lang="scss" scoped>
    @import '~@/variables';

    h1 {
        font-size: 60px;
        text-align: center;
        width: 100%;
        margin-bottom: 0;
    }

    .el-button {
        margin: 0px 10px;
        font-size: 18px;
        font-family: $font;
        color: $font-color;
    }

</style>

<template>
    <div>
        <el-row>
            <h1>The Guestbook Challenge</h1>
        </el-row>
        <el-row type="flex" justify="center">

            <div v-if="!authenticated">
                <el-col :span="4" style="border-right: 2px solid black;">
                    <el-button type="text" style="float: right;" @click="goTo('/login');">Login</el-button>
                </el-col>
                <el-col :span="4">
                    <el-button type="text" @click="goTo('/register');">Register</el-button>
                </el-col>
            </div>

            <div v-if="authenticated">
                <el-col :span="12" style="border-right: 2px solid black;">
                    <el-button type="text" style="float: right;">{{ name }}</el-button>
                </el-col>
                <el-col :span="12">
                    <el-button type="text" @click="logout();">Logout</el-button>
                </el-col>
            </div>
        </el-row>
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
            authenticated: {
                type: Boolean,
                default: false
            },
            name: {
                type: String,
                default: ''
            },
            email: {
                type: String,
                default: ''
            },
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Log user out
             *
             * @return {void}
             */
            logout() {
                document.getElementById('logout-form').submit();
            }
        }

    }
</script>
