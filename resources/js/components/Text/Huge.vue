<style lang="scss" scoped>
    @import '~@/variables';

    .clickable {
        cursor: pointer;

        &:hover {
            text-decoration: underline;
        }
    }

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
    <h1 @click="routeIfHref();" :class="{clickable: href !== ''}">{{ text }}</h1>
</template>

<script>
    import Text from './Text'
    export default {

        /**
         * Mixins
         *
         * @type {Array}
         */
        mixins: [
            Text
        ]
    }
</script>
