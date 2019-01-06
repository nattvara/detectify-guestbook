<style lang="scss" scoped>
    @import '~@/variables';

    .title {
        margin-bottom: 20px;
    }

    .el-button {
        margin-top: 30px;
    }

    .el-form.blur {
        filter: blur(2px);
    }

    .login-msg {
        width: 100%;
        text-align: center;
        margin-top: 0;
        margin-bottom: -75px;
        z-index: 2;
    }

    h2.auth {
        font-size: 18px;
    }

    .el-button.auth {
        margin: 0;
        padding: 0;
        display: inline;
        float: unset;
        position: unset;
        font-family: $font, serif;
        font-size: 18px;

        &:hover {
            text-decoration: underline;
        }
    }

    .el-dialog {
        z-index: 3;
    }

</style>

<template>
    <div>
        <transition name="el-fade-in">
            <el-row class="new" v-show="messageForm.show">
                <el-col :span="22" :offset="1">
                    <el-row class="title">
                        <h2>Write a message</h2>
                    </el-row>
                    <el-row v-if="!loggedIn()" class="login-msg">
                        <h2 class="auth">
                            <el-button type="text" class="auth" @click="login();">Login</el-button>
                            or
                            <el-button type="text" class="auth" @click="goTo('/register');">Register</el-button>
                            to write a message
                        </h2>
                    </el-row>
                    <el-row>
                        <el-form
                            v-loading="messageForm.loading"
                            ref="form"
                            status-icon
                            :class="{
                                blur: !loggedIn()
                            }"
                            :model="messageForm.form"
                            :rules="messageForm.rules"
                            size="large">
                            <el-form-item prop="text" :error="messageForm.errors.text">
                                <el-input type="textarea" autosize v-model="messageForm.form.text" placeholder="Enter your message text here"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button class="submit" type="primary" plain @click="post();">Post</el-button>
                                <el-button class="submit" type="default" plain @click="$emit('cancel');" v-if="showCancel">Cancel</el-button>
                            </el-form-item>
                        </el-form>
                    </el-row>
                </el-col>
            </el-row>
        </transition>
        <el-dialog title="Login" :visible.sync="loginDialog.display" :modal-append-to-body="false">
            <login-register
                size="large"
                :show-cancel="false"
                :show-buttons="false"></login-register>
        </el-dialog>
    </div>
</template>

<script>
    import LoginRegister from './../Forms/LoginRegisterContainer'
    export default {

        /**
         * Components data
         *
         * @return {Object}
         */
        data() {
            return {
                messageForm: {
                    loading: false,
                    show: false,
                    form: {
                        text: ''
                    },
                    errors: {
                        text: '',
                    },
                    rules: {
                        text: [
                            { validator: this.validate(), trigger: 'blur' },
                        ]
                    }
                },
                loginDialog: {
                    display: false
                }
            };
        },

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            replyTo: {
                type: String,
                default: ''
            },
            showCancel: {
                type: Boolean,
                default: false
            }
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
         * Component was mounted
         *
         * @return {void}
         */
        mounted() {
            this.messageForm.show = true;
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Validate text
             *
             * @return {Function}
             */
            validate() {
                return async (rule, value, callback) => {
                    if (this.messageForm.form.text.length == 0) {
                        callback();
                        return;
                    }
                    try {
                        var response = await axios.post('/messages/validate/text', {
                            text: this.messageForm.form.text
                        });
                        callback();
                    } catch (e) {
                        let error_string = '';
                        for (var i = e.response.data.errors.length - 1; i >= 0; i--) {
                            let error = e.response.data.errors[i];
                            if (error_string === '') {
                                error_string = error.human_friendly;
                                continue;
                            }
                            error_string += ', ' + error.human_friendly;
                        }
                        callback(new Error(error_string));
                    }
                }
            },

            /**
             * Post the message
             *
             * @return {void}
             */
            async post() {
                this.messageForm.loading = true;
                try {
                    let url = '/messages';
                    if (this.replyTo) {
                        url += '/' + this.replyTo;
                    }
                    var response = await axios.post(url, this.messageForm.form);
                    this.messageForm.form.text = '';
                    this.$emit('reload-messages');
                } catch (e) {
                    this.alertError(e.response.data.message);
                    for (var prop in this.messageForm.errors) {
                        this.messageForm.errors[prop] = '';
                    }
                    for (var i = e.response.data.errors.length - 1; i >= 0; i--) {
                        let error = e.response.data.errors[i];
                        if (this.messageForm.errors[error.field]) {
                            this.messageForm.errors[error.field] += ', ' + error.human_friendly;
                            continue;
                        }
                        this.messageForm.errors[error.field] += error.human_friendly;
                    }
                } finally {
                    this.messageForm.loading = false;
                }
            },

            /**
             * Show login dialog
             *
             * @return {void}
             */
            login() {
                this.loginDialog.display = true;
            }
        }
    }
</script>
