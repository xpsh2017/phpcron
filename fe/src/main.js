// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.

import Vue from 'vue'
import App from './App'
import ElementUI from 'element-ui'
import 'normalize.css'
import 'element-ui/lib/theme-default/index.css'
import router from './router'

window.axios = require('axios')
window.qs = require('qs')
if(process.env.NODE_ENV == 'development')
    window.axios.defaults.baseURL = 'http://paynexu.com/be/'
else
    window.axios.defaults.baseURL = 'http://ss-tool.meikaiinfotech.com/phpcron/be/'
// window.axios.defaults.baseURL = ''
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
Vue.config.productionTip = false
Vue.use(ElementUI)

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App/>',
  components: { App }
})
