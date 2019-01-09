<script>
    export default {

        /**
         * Components properties
         *
         * @type {Object}
         */
        props: {
            text: String,
            href: {
                type: String,
                default: ''
            }
        },

        /**
         * Components methods
         *
         * @type {Object}
         */
        methods: {

            routeIfHref() {
                if (!this.href) {
                    return;
                }
                this.goTo(this.href);
            }

        }

    }
</script>
