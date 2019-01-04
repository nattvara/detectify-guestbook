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

    .below_title {
        min-height: 150px;
        margin-bottom: -100px;
    }

    .name {
        float: right;
        pointer-events: none;
    }

</style>

<template>
    <div>
        <el-row>
            <h1>The Guestbook Challenge</h1>
        </el-row>

        <el-row class="below_title">

            <login-register v-if="!authenticated && showLoginRegister"></login-register>

            <el-row v-if="authenticated" type="flex" justify="center">
                <div>
                    <el-col :span="12" style="border-right: 2px solid black;">
                        <el-button type="text" class="name">{{ name }}</el-button>
                    </el-col>
                    <el-col :span="12">
                        <el-button type="text" @click="logout();">Logout</el-button>
                    </el-col>
                </div>
            </el-row>
        </el-row>
    </div>
</template>

<script>
    import LoginRegister from './../Forms/LoginRegisterContainer'
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
            showLoginRegister: {
                type: Boolean,
                default: true
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
         * Components
         *
         * @type {Object}
         */
        components: {
            LoginRegister
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
