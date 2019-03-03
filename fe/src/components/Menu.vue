<template>
  <div id="menu">
      <el-menu mode="vertical" router>
      <el-menu-item-group >
        <!-- <el-menu-item index="/control-center"><i class="el-icon-menu"></i> Control Center</el-menu-item> -->
        <el-menu-item index="/cron">Cron</el-menu-item>
        <el-menu-item index="/cron-log">Cron Log</el-menu-item>
        <el-menu-item index="/user-group">User Group</el-menu-item>
        <el-menu-item index="/cron-log-report">Cron Log Report</el-menu-item>
      </el-menu-item-group>
    </el-menu>
  </div>
</template>

<script>
export default {
  name: 'menu',
  data () {
    return {

    }
  }
}
</script>

<style scoped>
#menu {
  height: 100%;
}
</style>
