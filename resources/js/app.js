/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require('@babel/polyfill');

// Configure axios
window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
let csrf = document.head.querySelector('meta[name="csrf_token"]');
if (csrf) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf.content;
}


import Vue from 'vue';

// Element UI
import Element from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en'
Vue.use(Element, { locale });

// Font Awesome
import { library } from '@fortawesome/fontawesome-svg-core'
import { faReply, faShareAlt } from '@fortawesome/free-solid-svg-icons'
import { faGithub, faYoutube } from '@fortawesome/free-brands-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faReply);
library.add(faShareAlt);
library.add(faGithub);
library.add(faYoutube);
Vue.component('font-awesome-icon', FontAwesomeIcon);

// vue-scrollto
import VueScrollTo from 'vue-scrollto'
Vue.use(VueScrollTo);


// nattvara/detectify-guestbook components
Vue.component('page', require('./components/Page/Main.vue').default);
Vue.component('register-form', require('./components/Forms/Register.vue').default);
Vue.component('messages', require('./components/Messages/Main.vue').default);
Vue.component('huge', require('./components/Text/Huge.vue').default);
Vue.component('medium', require('./components/Text/Medium.vue').default);

Vue.mixin({
    data() {
        return {
            mobile: null
        };
    },
    methods: {

        /**
         * Goto path
         *
         * @param  {String}  path
         * @param  {Boolean} openInNewTab
         * @return {void}
         */
        goTo(path, openInNewTab = false) {
            if (openInNewTab) {
                var win = window.open(path, '_blank');
                win.focus();
                return;
            }
            window.location.href = path;
        },

        /**
         * Check if use logged in
         *
         * @return {Boolean}
         */
        loggedIn() {
            return window.authenticated;
        },

        /**
         * Pause execution (ish) for x milliseconds
         *
         * @param  {int} milliseconds
         * @return {Promse}
         */
        sleep(milliseconds) {
            return new Promise(resolve => setTimeout(resolve, milliseconds));
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

        /**
         * If on a mobile device
         *
         * @return {Boolean}
         */
        onMobile() {
            if (!this.mobile) {
                this.mobile = window.innerWidth <= 699;
                setInterval(() => {
                    this.mobile = window.innerWidth <= 699;
                }, 2000);
            }
            return this.mobile;
        }
    }
});


new Vue({
    el: '#app'
});
