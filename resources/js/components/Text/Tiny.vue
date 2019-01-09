<style lang="scss" scoped>
    @import '~@/variables';

    .clickable {
        cursor: pointer;

        &:hover {
            text-decoration: underline;
        }
    }

    h3 {
        width: 100%;
        text-align: center;
        font-size: 20px;
        margin: 0;

        @media only screen and (max-width: $mobile-break) {
            font-size: 15px;
        }
    }

</style>

<template>
    <h3 @click="routeIfHref();" :class="{clickable: href !== ''}">{{ text }}</h3>
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
