<style lang="scss" scoped>
    @import '~@/variables';

    .el-main {
        max-width: 1000px;
        min-height: 500px;
        margin: auto;
        width: 100%;
        z-index: 2;
        padding: 0px 20px;
    }

    .el-header {
        height: 100% !important;
        z-index: 1;
        padding: 10px;
        margin-bottom: 30px;
    }

    .el-footer {
        padding: 0;
        margin: 0;
        margin-top: 50px;
        background-color: mix($primary, black, 95%);
        padding-top: 80px;

        .inside {
            text-align: center;
            margin: auto;
            width: 100%;
            max-width: 1000px;

            * {
                color: mix($font-color, mix($primary, black, 95%), 50%);
            }

            h3 {
                display: block;
                width: 100%;

                a {
                    text-decoration: none;
                }
            }

            p {
                width: 100%;

                a {
                    text-decoration: none;

                    &:hover {
                        text-decoration: underline;
                    }
                }
            }
        }
    }

</style>

<template>
    <el-container>
        <el-header>
            <page-header
                :show-login-register="showLoginRegister"
                :name="name"
                :email="email"></page-header>
        </el-header>

        <el-main><slot></slot></el-main>

        <el-footer height="300px">
            <div class="inside">
                <h3>
                    <a href="/">
                        The Guestbook Challenge
                    </a>
                </h3>
                <p>
                    <a href="https://github.com/nattvara/detectify-guestbook" target="_blank">
                        Source Code <font-awesome-icon :icon="['fab', 'github']" />
                    </a>
                </p>
                <p>
                    <a href="https://www.youtube.com/PewDiePie" target="_blank">
                        You should also subscribe to pewdiepie <font-awesome-icon :icon="['fab', 'youtube']" />
                    </a>
                </p>
            </div>
        </el-footer>
    </el-container>
</template>

<script>
    import PageHeader from './Header'
    export default {

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
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
         * Components.
         *
         * @type {Object}
         */
        components: {
            PageHeader
        }

    }
</script>
