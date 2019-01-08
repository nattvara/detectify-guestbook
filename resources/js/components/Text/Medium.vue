<style lang="scss" scoped>
    @import '~@/variables';

    h2 {
        width: 100%;
        text-align: center;
        font-size: 30px;
        margin: 0;

        @media only screen and (max-width: $mobile-break) {
            font-size: 20px;
        }
    }

</style>

<template>
    <h2>{{ text }}</h2>
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
