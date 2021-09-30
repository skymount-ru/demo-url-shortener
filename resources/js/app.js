require('./bootstrap')

import Vue from 'vue'
import UrlRegistrationForm from './vue-components/UrlRegistrationForm'

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

new Vue({
    el: '#app',
    components: {
        UrlRegistrationForm,
    }
})
