<template>
  <div id="home">
      <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
        </el-breadcrumb>
        welcome!
  </div>
</template>

<script>
export default {
  name: 'home',
  created() {
    alert(1232)
  },
  data () {
    return {
      msg: 'Welcome to Your Vue.js App',
      formInline: {
          user: '',
          region: ''
        }
    }
  },
    methods: {
      onSubmit() {
        console.log('submit!');
      }
    }
}
</script>

<style scoped>
.query-form {
    margin-top: 20px;
    padding-top: 20px;
    background-color: #f2f2f2;
}
</style>
