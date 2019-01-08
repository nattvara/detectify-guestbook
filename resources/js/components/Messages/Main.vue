<style lang="scss" scoped>
    @import '~@/variables';

    .new {
        margin-top: 20px;
    }

    div {
        @media only screen and (max-width: $mobile-break) {
            margin-left: 1px;
        }
    }

</style>

<template>
    <div v-loading.fullscreen.lock="!firstLoad">

        <message
            v-for="message in messages"
            v-if="!message.parent_id || rootId == message.id"
            :key="message.id"
            :id="message.id"
            :is-root="true"
            :parent-id="message.id"
            :author="message.author"
            :author-id="message.author_id"
            :text="message.text"
            :votes="message.votes"
            :created-at="message.created_at"
            :children="message.children"
            :loaded-at="fetchedAt"
            @reload-messages="fetch();"
            ></message>

        <new-message
            class="new"
            :loaded-at="fetchedAt"
            @reload-messages="fetch();" v-if="rootId === ''"></new-message>
    </div>
</template>

<script>
    import Message from './Message'
    import NewMessage from './NewMessage'
    export default {

        /**
         * Components.
         *
         * @type {Object}
         */
        components: {
            Message,
            NewMessage
        },

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            rootId: {
                type: String,
                default: ''
            }
        },

        /**
         * Components data
         *
         * @return {Object}
         */
        data() {
            return {
                messages: [],
                loading: false,
                firstLoad: false,
                fetchedAt: 0
            };
        },

        /**
         * Component was mounted
         *
         * @return {void}
         */
        async mounted() {
            while (true) {
                await this.fetch();
                this.firstLoad = true;
                await this.sleep(5000);
            }
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            /**
             * Fetch messages
             *
             * @return {void}
             */
            async fetch() {
                if (this.loading) {
                    return;
                }
                try {
                    this.loading = true;
                    let url = '/messages';
                    if (this.rootId) {
                        url += '/' + this.rootId + '?format=json';
                    }
                    let response = await axios.get(url);
                    this.messages = response.data.messages;
                } catch (e) {
                    this.alertError('Failed to load messages');
                } finally {
                    this.fetchedAt  = (new Date()).getTime();
                    this.loading    = false;
                }
            }

        }
    }
</script>
