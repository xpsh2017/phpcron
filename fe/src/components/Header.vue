<template>
  <header>
    <div class="container">
      <el-row :gutter="20">
          <el-col :span="24">
            <a href="#" class="logo">PHPCron <el-tag>V1.2</el-tag></a>
          </el-col>
      </el-row>
    </div>
  </header>
</template>

<script>
export default {
  name: 'header',
  data () {
    return {
      msg: ''
    }
  }
}
</script>

<style scoped>
  header {
    height: 80px;
    background-color: #20a0ff;
    line-height: 80px;
  }
  a.logo {
    font-size: 24px;
    font-weight: blod;
  }
</style>
