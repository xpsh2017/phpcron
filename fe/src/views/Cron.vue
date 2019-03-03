<template>
  <div id="cron">
      <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
          <el-breadcrumb-item>Cron</el-breadcrumb-item>
      </el-breadcrumb>
      <el-form :inline="true" class="query-form">
          <el-form-item label="IsActive">
            <el-select
              clearable
              v-model="search.IsActive">
              <el-option
                v-for="item in statusOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="AlertType">
            <el-select
              clearable
              v-model="search.AlertType">
              <el-option
                v-for="item in alertTypeOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Execute User">
            <el-select
              clearable
              v-model="search.UserName">
              <el-option
                v-for="item in UserNameOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">Search</el-button>
            <el-button type="success" @click="handleAdd">Add</el-button>
          </el-form-item>
        </el-form>
        <el-table
          :data="list"
          border
          highlight-current-row
          >
          <el-table-column
            prop="Name"
            label="Name"
            width="150">
          </el-table-column>
          <el-table-column
            prop="Server"
            label="Server"
            width="150">
          </el-table-column>
          <el-table-column
            prop="TimeString"
            label="TimeString"
            width="150">
          </el-table-column>
          <el-table-column
            prop="CommandString"
            label="CommandString">
          </el-table-column>
          <el-table-column
            prop="TimeOutSecond"
            label="TimeOutSecond"
            width="100">
          </el-table-column>
          <el-table-column
            prop="IsActive"
            label="IsActive"
            width="150">
          </el-table-column>
          <el-table-column
            prop="AlertType"
            label="AlertType"
            width="150">
          </el-table-column>
          <el-table-column
            prop="UserName"
            label="Execute User"
            width="150">
          </el-table-column>
          <el-table-column
            label="Operation"
            width="150">
              <template scope="scope">
                <el-button type="info" @click="handleEdit(scope.row)">Edit</el-button>
              </template>
          </el-table-column>
        </el-table>
        <div class="page_block">
          <el-pagination
            @size-change="handleSizeChange"
            @current-change="handleCurrentPageChange"
            :current-page="currentPage"
            :page-sizes="pageSizes"
            :page-size="pageSize"
            layout="total, sizes, prev, pager, next, jumper"
            :total="count">
          </el-pagination>
        </div>
        <!-- 对话框，add edit -->
        <el-dialog :title="formTitle" :visible.sync="dialogCronFormVisible" class="mk_dialog" @open="handleCronFormOpen" @close="handleCronFormClose">
          <el-form :model="cronForm">
            <el-form-item label="脚本名字" :label-width="formLabelWidth">
              <el-input v-model="cronForm.Name" class="small-input" auto-complete="off"></el-input>
            </el-form-item>
            <el-form-item label="Cron表达式" :label-width="formLabelWidth">
              <el-input v-model="cronForm.TimeString" class="small-input" auto-complete="off"></el-input>
            </el-form-item>
            <el-form-item label="执行命令" :label-width="formLabelWidth">
              <el-input
                type="textarea"
                :rows="2"
                placeholder="请输入命令"
                v-model="cronForm.CommandString">
              </el-input>
            </el-form-item>
            <el-form-item label="日志文件" :label-width="formLabelWidth">
              <el-input
                type="textarea"
                :rows="2"
                placeholder="请输入路径+文件名, 若不填则系统指定"
                v-model="cronForm.LogFileString">
              </el-input>
            </el-form-item>
            <el-form-item label="超时时间" :label-width="formLabelWidth">
              <el-input v-model="cronForm.TimeOutSecond" class="tiny-input" auto-complete="off"></el-input>
            </el-form-item>
            <el-form-item label="脚本状态" :label-width="formLabelWidth">
              <el-switch
                v-model="cronForm.IsActive"
                on-color="#13ce66"
                off-color="#ff4949"
                on-value="YES"
                off-value="NO">
              </el-switch>
            </el-form-item>
            <el-form-item label="执行用户" :label-width="formLabelWidth">
              <el-select v-model="cronForm.UserName" placeholder="请选择">
                <el-option
                  v-for="item in executeOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="报警类型" :label-width="formLabelWidth">
              <el-select v-model="cronForm.AlertType" placeholder="请选择">
                <el-option
                  v-for="item in alertTypeOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="邮件发送给" :label-width="formLabelWidth" v-if="cronForm.AlertType == 'EMAIL'">
              <el-select v-model="cronForm.AlertUsers" placeholder="请选择" multiple>
                <el-option
                  v-for="item in userOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              <el-popover
                ref="popoverAddUser"
                placement="top"
                width="300"
                @hide="handleUserClose"
                v-model="visibleAddUser">
                <el-form :model="boUser">
                  <el-form-item label="姓名" :label-width="formLabelWidth2">
                    <el-input v-model="boUser.Name"></el-input>
                  </el-form-item>
                  <el-form-item label="邮箱" :label-width="formLabelWidth2">
                    <el-input v-model="boUser.Email"></el-input>
                  </el-form-item>
                </el-form>
                <div style="float: right">
                  <el-button size="mini" type="text" @click="visibleAddUser = false">取消</el-button>
                  <el-button type="primary" size="mini" @click="handleUserSubmit">确定</el-button>
                </div>
              </el-popover>
              <el-button v-popover:popoverAddUser type="success" style="margin-left:30px;">添加</el-button>
            </el-form-item>
            <el-form-item label="站点类型" :label-width="formLabelWidth">
              <el-select v-model="cronForm.Server" placeholder="请选择">
                <el-option
                        v-for="item in serverOptions"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                </el-option>
              </el-select>
              <el-popover
                      ref="popoverAddServer"
                      placement="top"
                      width="300"
                      @hide="handleServerClose"
                      v-model="visibleAddServer">
                <el-form :model="boServer">
                  <el-form-item label="Server" :label-width="formLabelWidth2">
                    <el-input v-model="boServer.server"></el-input>
                  </el-form-item>
                </el-form>
                <div style="float: right">
                  <el-button size="mini" type="text" @click="visibleAddServer = false">取消</el-button>
                  <el-button type="primary" size="mini" @click="handleServerSubmit">确定</el-button>
                </div>
              </el-popover>
              <el-button v-popover:popoverAddServer type="info" style="margin-left:30px;">添加</el-button>
            </el-form-item>
            <el-form-item label="成功执行标志" :label-width="formLabelWidth">
              <el-input
                type="textarea"
                :rows="2"
                placeholder="请输入一个字符串，并在程序成功执行时输出这个字符串，代表程序成功执行, 若不填不检测"
                v-model="cronForm.SuccFlag">
              </el-input>
            </el-form-item>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <el-button @click="dialogCronFormVisible = false">取 消</el-button>
            <el-button :type="formButton.type" @click="handleSubmit" v-html="formButton.text "></el-button>
          </div>
        </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'cron',
  created() {
    this.getList()
    this.setExecuteUser()
  },
  data () {
    return {
      list: [],
      currentPage: 1,
      pageSizes: [10, 20, 50, 100],
      pageSize: 10,
      count: 0,
      dialogCronFormVisible: false,
      formTitle: '添加 Cron',
      cronForm: {
        UUID: '',
        Name: '',
        TimeString: '0 * * * *',
        TimeOutSecond: 0,
        CommandString: '',
        LogFileString: '',
        IsActive: 'YES',
        AlertType: 'NOALERT',
        UserName: '',
        AlertUsers: [],
        Server: '',
        SuccFlag: ''
      },
      formLabelWidth: '120px',
      formLabelWidth2: '50px',
      formButton: {
        type: 'primary',
        text: '确定'
      },
      currentRow: null,
      alertTypeOptions: [
        {
          value: 'NOALERT',
          label: '不需要报警'
        },
        {
          value: 'EMAIL',
          label: 'Email报警'
        },
        {
          value: 'SHELLCMD',
          label: '调用命令报警'
        },
      ],
      visibleAddUser: false,
      boUser: {
        Name: '',
        Email: ''
      },
      userOptions: [

      ],
      executeOptions:[],
      serverOptions:[],
      visibleAddServer: false,
      boServer: {

      },
      statusOptions: [
        {
          label: 'YES',
          value: 'YES'
        },
        {
          label: 'NO',
          value: 'NO'
        }
      ],
      UserNameOptions: [],
      search: {
        IsActive: '',
        AlertType: '',
        UserName: ''
      }
    }
  },
  methods: {
    getList() {
      var that = this
      var isactive_str, alerttype_str, username_str = ''
      if(this.search.IsActive !== undefined || this.search.IsActive.length > 0)    isactive_str = '&isactive='+this.search.IsActive
      if(this.search.AlertType !== undefined || this.search.AlertType.length > 0)    alerttype_str = '&alerttype='+this.search.AlertType
      if(this.search.UserName !== undefined || this.search.UserName.length > 0)    username_str = '&username='+this.search.UserName
      window.axios.get('?action=cron&func=list&page='+this.currentPage+'&size='+this.pageSize+isactive_str+alerttype_str+username_str)
        .then(function(response){
          that.list = response.data.list
          that.count = response.data.count
        }).catch(function(error){

        })
    },
    handleSizeChange(size) {
      this.pageSize = size
      this.getList()
    },
    handleCurrentPageChange(currentPage) {
      this.currentPage = currentPage
      this.getList()
    },
    handleAdd() {
      this.formTitle = '添加 Cron'
      this.dialogCronFormVisible = true
    },
    handleEdit(row) {
      var that = this
      this.formTitle = '编辑 Cron'
      this.$set(row, 'AlertUsers', [])
      this.cronForm = row
      var url = '?action=user&func=list&cron_id='+row.UUID
      window.axios.get(url)
        .then(function(response){
          if(response.data.list.length >0)
          {
            that.cronForm.AlertUsers = response.data.list.map(item => {
              return item.UUID
            })
          }
        }).catch(function(error){

        })
      this.dialogCronFormVisible = true
    },
    handleCronFormOpen() {
      var that = this
      var url = '?action=user&func=list'
      window.axios.get(url)
        .then(function(response){
          if(response.data.list.length > 0)
          {
            that.userOptions = response.data.list.map(item => {
              return { value: item.UUID, label: item.Name }
            })
          }
        }).catch(function(error){

        })
      var url = '?action=group&func=list'
      window.axios.get(url)
        .then(function(response){
          if(response.data.list.length > 0)
          {
            that.executeOptions = response.data.list.map(item => {
              return { value: item.GroupName, label: item.GroupName }
            })
          }
        }).catch(function(error){

        })
      var url = '?action=server&func=list'
      window.axios.get(url)
              .then(function(response){
                if(response.data.list.length > 0)
                {
                  that.serverOptions = response.data.list.map(item => {
                            return { value: item.Server, label: item.Server }
                          })
                }
              }).catch(function(error){

      })
    },
    handleCronFormClose() {
      this.cronForm = {
        UUID: '',
        Name: '',
        TimeString: '0 * * * *',
        TimeOutSecond: 0,
        CommandString: '',
        LogFileString: '',
        IsActive: 'YES',
        AlertType: 'NOALERT',
        UserName: '',
        AlertUsers: [],
        SuccFlag: ''
      },
      this.formTitle = '添加 Cron'
      this.formButton = {
        type: 'primary',
        text: '确定'
       }
       this.userOptions = []
    },
    handleSubmit() {
      var that = this
      var uuid = this.cronForm.UUID
      var formData = window.qs.stringify(this.cronForm)
      var url = ''
       if(uuid.length > 0)
       {
          // update
          url = '?action=cron&func=update'
       }else{
          // add
          url = '?action=cron&func=insert'
       }
       this.formButton = {
        type: 'info',
        text: '<i class="el-icon-loading"></i>'
       }
       window.axios.post(url, formData)
        .then(function(response){
          that.getList()
          that.formButton = {
            type: 'success',
            text: '成功'
           }
           that.$message({
            type: 'success',
            message: '编辑成功!'
          })
           that.dialogCronFormVisible = false;
        }).catch(function(error){
          that.$message.error(error.response.data.content)
          that.formButton = {
            type: 'danger',
            text: '重试'
           }
        })
    },
    handleUserSubmit() {
      var that = this
      var formData = window.qs.stringify(this.boUser)
      var url = '?action=user&func=insert'
      window.axios.post(url, formData)
        .then(function(response){
          var userOption = new Object()
          userOption.value  = response.data.content.UUID
          userOption.label  = response.data.content.Name

          that.visibleAddUser = false
          that.userOptions.push(userOption)
          that.cronForm.AlertUsers.push(userOption.value)
          
        }).catch(function(error){

        })
    },
    handleUserClose() {
      this.boUser = {
        Name: '',
        Email: ''
      }
    },
    handleSearch() {
      this.getList()
    },
    setExecuteUser() {
      var that = this
      var url = '?action=group&func=list'
      window.axios.get(url)
        .then(function(response){
          if(response.data.list.length > 0)
          {
            that.UserNameOptions = response.data.list.map(item => {
              return { value: item.GroupName, label: item.GroupName }
            })
          }
        }).catch(function(error){

        })
    },
    handleServerClose() {

    },
    handleServerSubmit() {
      var that = this
      var url = '?action=server&func=insert'
      var formData = window.qs.stringify(this.boServer)
      window.axios.post(url, formData)
              .then(function(response){
                var serverOption = new Object()
                serverOption.value  = response.data.content.UUID
                serverOption.label  = response.data.content.Server

                that.visibleAddServer = false
                that.serverOptions.push(serverOption)
                that.cronForm.Server.push(serverOption.value)
              }).catch(function(error){
        that.$message.error(error.data.content)
      })
    },

    test(val) {
      alert(val)
    }
  }
}
</script>

<style scoped>

</style>
