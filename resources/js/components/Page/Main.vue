<style lang="scss" scoped>
    @import '~@/variables';

    .el-main {
        max-width: 1000px;
        margin: auto;
        width: 100%;
    }

    .el-header {
        height: 100% !important;
    }

</style>

<template>
    <el-container>
        <el-header>
            <page-header
                :authenticated="authenticated"
                :name="name"
                :email="email"></page-header>
        </el-header>

        <el-main><slot></slot></el-main>

        <el-footer></el-footer>
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
         * Components.
         *
         * @type {Object}
         */
        components: {
            PageHeader
        }

    }
</script>
