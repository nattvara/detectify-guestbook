<style lang="scss" scoped>
    @import '~@/variables';

    h1 {
        font-size: 60px;
        text-align: center;
        width: 100%;
        margin-bottom: 0;
    }

    .text.el-button {
        margin: 0px 10px;
        font-size: 18px;
        font-family: $font;
        color: $font-color;
    }

    .el-form {
        margin: auto;
    }

    .submit {
        margin-top: 4px;
    }

</style>

<template>
    <div>
        <transition name="el-zoom-in-top">
            <el-row type="flex" justify="center" v-show="buttons.show">
                <el-col :span="4" style="border-right: 2px solid black;">
                    <el-button class="text" type="text" style="float: right;" @click="showLoginForm();">Login</el-button>
                </el-col>
                <el-col :span="4">
                    <el-button class="text" type="text" @click="goTo('/register');">Register</el-button>
                </el-col>
            </el-row>
        </transition>

        <transition name="el-zoom-in-bottom">
            <el-row type="flex" justify="center" v-show="loginForm.show">
                <el-form
                    ref="form"
                    size="mini"
                    :model="loginForm.form"
                    :rules="loginForm.rules"
                    :inline="true">
                    <el-form-item prop="email" class="login" :error="loginForm.errors.email">
                        <el-input type="email" v-model="loginForm.form.email" placeholder="Email"></el-input>
                    </el-form-item>
                    <el-form-item prop="password" class="login" :error="loginForm.errors.password">
                        <el-input type="password" v-model="loginForm.form.password" autocomplete="off" placeholder="Password"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button class="submit" type="primary" plain @click="attemptLogin();">Login</el-button>
                        <el-button class="submit" type="secondary" plain @click="hideLoginForm();">Cancel</el-button>
                    </el-form-item>
                </el-form>
            </el-row>
        </transition>
    </div>
</template>

<script>
    export default {

        /**
         * Components data
         *
         * @return {Object}
         */
        data() {
            return {
                buttons: {
                    show: true,
                },
                loginForm: {
                    show: false,
                    form: {
                        email: '',
                        password: '',
                    },
                    errors: {
                        email: '',
                        password: ''
                    },
                    rules: {
                        email: [
                            { required: true, message: 'Please enter an email', trigger: 'blur' },
                            { type: 'email', message: 'Please enter a valid email', trigger: 'blur' }
                        ],
                        password: [
                            { required: true, message: 'Please enter your password', trigger: 'blur' },
                        ]
                    }
                },
            };
        },

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {},

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Hide login form
             *
             * @return {void}
             */
            async hideLoginForm() {
                this.loginForm.show = false;
                await this.sleep(300);
                this.buttons.show = true;
            },

            /**
             * Show login form
             *
             * @return {void}
             */
            async showLoginForm() {
                this.buttons.show = false;
                await this.sleep(300);
                this.loginForm.show = true;
            },

            /**
             * Attempt login
             *
             * @return {void}
             */
            async attemptLogin() {
                try {
                    var response = await axios.post('/login', {
                        email: this.loginForm.form.email,
                        password: this.loginForm.form.password,
                    });
                } catch (e) {
                    for (var prop in this.loginForm.errors) {
                        this.loginForm.errors[prop] = '';
                    }
                    for (var i = e.response.data.errors.length - 1; i >= 0; i--) {
                        let error = e.response.data.errors[i];
                        if (this.loginForm.errors[error.field]) {
                            this.loginForm.errors[error.field] += ', ' + error.human_friendly;
                            continue;
                        }
                        this.loginForm.errors[error.field] += error.human_friendly;
                    }
                    return;
                }

                if (response.data.login) {
                    window.location.reload();
                    return;
                }

                this.alertError(response.data.message);
            }

        }

    }
</script>
