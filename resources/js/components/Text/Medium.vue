<style lang="scss" scoped>
    @import '~@/variables';

    .clickable {
        cursor: pointer;

        &:hover {
            text-decoration: underline;
        }
    }

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
    <h2 @click="routeIfHref();" :class="{clickable: href !== ''}">{{ text }}</h2>
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
