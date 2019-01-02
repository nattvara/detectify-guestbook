<style lang="scss" scoped>
    @import '~@/variables';
</style>

<template>
    <div>
        <message
            text="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam"
            author="Lorem ipsum"></message>

        <message
            text="Sed ut perspiciatis unde omnis iste natus error sit voluptatem"
            author="Nemo enim ipsam">
                <div>
                    <message
                        text="Sed ut perspiciatis unde omnis iste natus error sit voluptatem"
                        author="Nemo enim ipsam"></message>

                    <message
                        text="Sed quia non"
                        author="Ipsam"></message>
                </div>
            </message>

        <message
            text="Sed quia non"
            author="Ipsam"></message>

        <message
            text="Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur"
            author="Ipsam"></message>
    </div>
</template>
<script>
    import Message from './Message'
    export default {

        /**
         * Components.
         *
         * @type {Object}
         */
        components: {
            Message
        }

    }
</script>
