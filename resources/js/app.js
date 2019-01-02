/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Vue from 'vue';
import Element from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en'

Vue.use(Element, { locale })
Vue.component('page', require('./components/Page/Main.vue').default);
Vue.component('messages', require('./components/Messages/Main.vue').default);

Vue.mixin({
    methods: {

        /**
         * Goto path
         *
         * @param  {String} path
         * @return {void}
         */
        goTo(path) {
            window.location.href = path;
        },

        /**
         * Add an alert method used across components to display errors
         *
         * @param  {string} message
         * @return {void}
         */
        alertError(message, type = 'error', duration = 3000, showClose = false) {
            this.$message({
                showClose: true,
                message: message,
                type: type,
                duration: duration,
                showClose: showClose
            });
        },
    }
});


new Vue({
    el: '#app'
});
