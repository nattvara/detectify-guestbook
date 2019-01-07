<style lang="scss" scoped>
    @import '~@/variables';

    h2 {
        font-size: 40px;
    }

    .el-form {
        width: 100%;
    }

    .el-button {
        margin-top: 50px;
    }

</style>

<template>
    <transition name="el-fade-in">
        <div v-show="registerForm.show">
            <el-row>
                <h2>Register</h2>
            </el-row>
            <el-row type="flex" justify="left">
                <el-form
                    ref="form"
                    status-icon
                    :model="registerForm.form"
                    :rules="registerForm.rules"
                    size="large">
                    <el-row>
                        <el-col :span="8">
                            <el-form-item prop="email" label="Email" :error="registerForm.errors.email">
                                <el-input type="email" v-model="registerForm.form.email" placeholder="name@example.com"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="1">
                            <el-form-item prop="name" label="Full Name" :error="registerForm.errors.name">
                                <el-input type="text" v-model="registerForm.form.name" placeholder="John Doe"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item prop="password" label="Password" :error="registerForm.errors.password">
                                <el-input type="password" v-model="registerForm.form.password" autocomplete="off" placeholder="Just not 'password123'"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8" :offset="1">
                            <el-form-item prop="password_repeat" label="Repeat Password" :error="registerForm.errors.password_repeat">
                                <el-input type="password" v-model="registerForm.form.password_repeat" autocomplete="off" placeholder="Waaay easier with a password manager"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item>
                        <el-button class="submit" type="primary" plain @click="register();">Register</el-button>
                    </el-form-item>
                </el-form>
            </el-row>
        </div>
    </transition>
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
                registerForm: {
                    show: false,
                    form: {
                        email: '',
                        name: '',
                        password: '',
                        password_repeat: '',
                    },
                    errors: {
                        email: '',
                        name: '',
                        password: '',
                        password_repeat: '',
                    },
                    rules: {
                        email: [
                            { validator: this.validate('email'), trigger: 'blur' },
                        ],
                        name: [
                            { validator: this.validate('name'), trigger: 'blur' },
                        ],
                        password: [
                            { validator: this.validate('password'), trigger: 'blur' },
                        ],
                        password_repeat: [
                            { validator: this.validate('password_repeat'), trigger: 'blur' },
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
         * Component was mounted
         *
         * @return {void}
         */
        mounted() {
            this.registerForm.show = true;
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Validate a field
             *
             * Called by the el-form, see rules property on registerForm
             *
             * @param  {String} field
             * @return {Function}
             */
            validate(field) {
                return async (rule, value, callback) => {
                    try {
                        var data = {};
                        for (var prop in this.registerForm.form) {
                            data[prop] = this.registerForm.form[prop];
                        }
                        var response = await axios.post('/register/validate/' + field, data);
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
             * Register user
             *
             * @return {void}
             */
            async register() {
                try {
                    var response = await axios.post('/register', this.registerForm.form);
                    window.location.reload();
                } catch (e) {
                    this.alertError(e.response.data.message);
                    for (var prop in this.registerForm.errors) {
                        this.registerForm.errors[prop] = '';
                    }
                    for (var i = e.response.data.errors.length - 1; i >= 0; i--) {
                        let error = e.response.data.errors[i];
                        if (this.registerForm.errors[error.field]) {
                            this.registerForm.errors[error.field] += ', ' + error.human_friendly;
                            continue;
                        }
                        this.registerForm.errors[error.field] += error.human_friendly;
                    }
                }
            }


        }

    }
</script>
