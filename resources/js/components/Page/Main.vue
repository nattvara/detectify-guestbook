<style lang="scss" scoped>

    .el-main {
        max-width: 1000px;
        margin: auto;
        width: 100%;
    }

    .el-header {
        margin-bottom: 60px;
    }

</style>

<template>
    <el-container>
        <el-header>
            <page-header></page-header>
        </el-header>

        <el-main><slot></slot></el-main>

        <el-footer></el-footer>
    </el-container>
</template>
<script>
    import PageHeader from './Header'
    export default {

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
