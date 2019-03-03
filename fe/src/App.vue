<template>
  <div id="app">
    <my-header></my-header>
    <main>
      <el-row class="main">
        <el-col :span="2" class="menu-block">
          <my-menu></my-menu>
        </el-col>
        <el-col :span="22" class="main-block">
          <router-view></router-view>
        </el-col>
      </el-row>
    </main>
  </div>
</template>

<script>
import myHeader from './components/Header'
import myMenu from './components/Menu'
export default {
  name: 'app',
  components: {
    myHeader,
    myMenu
  }
}
</script>

<style>
.el-menu, body, html, main, .main, .menu-block {
  height: 100%;
}
.menu-block {
  padding-right: 20px;
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  height: 100%;
}
a {
  text-decoration: none;
  color: #fff;
}
table a {
  color: #000;
}
.container {
  padding: 0 20px;
}
.main {

}
.main-block {
  margin-top: 20px;
}
.cell {
  text-align: center;
}
.el-table .running-row {
    background: #13CE66;
}
.el-table .failed-row {
    background: #FF4949;
}
.query-form {
  margin-top: 20px;
  margin-bottom: 20px;
  padding-top: 20px;
  padding-left: 10px;
  padding-right: 10px;
  background-color: #f2f2f2;
}
.query-form a {
  color: #000;
}
.page_block {
  margin-top: 20px;
  margin-bottom: 20px;
  float: right;
}
.mk_dialog .small-input{
  width: 300px;
}
.mk_dialog .tiny-input{
  width: 100px;
}
</style>
