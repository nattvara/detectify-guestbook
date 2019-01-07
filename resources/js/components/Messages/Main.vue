<style lang="scss" scoped>
    @import '~@/variables';
</style>

<template>
    <div>
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
            :responses="responses"
            :messages="messages"
            @reload-messages="fetch();"
            ></message>

        <new-message @reload-messages="fetch();" v-if="rootId === ''"></new-message>
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
                messages: []
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
                try {
                    let url = '/messages';
                    if (this.rootId) {
                        url += '/' + this.rootId + '?format=json';
                    }
                    let response = await axios.get(url);
                    this.messages = response.data.messages;
                } catch (e) {
                    this.alertError('Failed to load messages');
                }
            },

            /**
             * Get responses from array of messages
             *
             * @param  {Array}  messages Array to select from
             * @param  {String} parentId Parent messages should be in response to
             * @return {Array}
             */
            responses(messages, parentId) {
                var responses = [];
                for (var i = messages.length - 1; i >= 0; i--) {
                    if (messages[i].parent_id === parentId) {
                        responses.push(messages[i]);
                    }
                }
                return responses;
            }

        }

    }
</script>
