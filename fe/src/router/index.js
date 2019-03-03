import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/views/Home'
import Cron from '@/views/Cron'
import CronLog from '@/views/CronLog'
import ControlCenter from '@/views/ControlCenter'
import UserGroup from '@/views/UserGroup'
import CronLogReport from '@/views/CronLogReport'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home
    },
    {
      path: '/cron',
      name: 'Cron',
      component: Cron
    },
    {
      path: '/cron-log',
      name: 'CronLog',
      component: CronLog
    },
    {
      path: '/control-center',
      name: 'ControlCenter',
      component: ControlCenter
    },
    {
      path: '/user-group',
      name: 'UserGroup',
      component: UserGroup
    },
    {
      path: '/cron-log-report',
      name: 'CronLogReport',
      component: CronLogReport
    },
  ]
})
