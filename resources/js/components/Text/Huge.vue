<style lang="scss" scoped>
    @import '~@/variables';

    h1 {
        width: 100%;
        text-align: center;
        font-size: 60px;
        margin: 0;

        @media only screen and (max-width: $mobile-break) {
            font-size: 40px;
        }
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
