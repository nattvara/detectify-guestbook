<style lang="scss" scoped>
    @import '~@/variables';

    h1 {
        width: 100%;
        text-align: center;
        font-size: 60px;
        margin: 0;
    }

</style>

<template>
    <h1>{{ text }}</h1>
</template>

<script>
    export default {

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            text: String
        }

    }
</script>
