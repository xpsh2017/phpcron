<template>
    <div id="control-center">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
          <el-breadcrumb-item>User</el-breadcrumb-item>
        </el-breadcrumb>
        <el-form :inline="true" class="query-form" :model="search">
            <el-form-item label="StartTime">
                <el-date-picker
                        v-model="search.date"
                        type="datetimerange"
                        :picker-options="dateOptions"
                        placeholder="choose time range"
                        range-separator="~"
                        align="right">
                </el-date-picker>
            </el-form-item>
            <el-form-item label="Status">
                <el-select
                        clearable
                        v-model="search.status">
                    <el-option
                            v-for="item in processStatusOptions"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Name">
                <el-input v-model="search.name"></el-input>
            </el-form-item>
            <el-form-item label="Command">
                <el-input v-model="search.commandstring"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="handleSearch">Search</el-button>
            </el-form-item>
      </el-form>
      <el-table
        :data="list"
        border
        highlight-current-row
        >
        <el-table-column
            prop="CrontabName"
            label="CrontabName">
        </el-table-column>
         <el-table-column
            prop="Server"
            label="Server">
          </el-table-column>
          <el-table-column
            prop="CreatedTime"
            label="CreatedTime">
          </el-table-column>
          <el-table-column
            label="Operation">
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
        <el-dialog :title="formTitle" :visible.sync="dialogUserFormVisible" class="mk_dialog" @open="handleUserFormOpen" @close="handleUserFormClose">
          <el-form :model="userForm">
            <el-form-item label="Name" :label-width="formLabelWidth">
              <el-input v-model="userForm.Name" class="small-input" auto-complete="off"></el-input>
            </el-form-item>
            <el-form-item label="Email" :label-width="formLabelWidth">
              <el-input v-model="userForm.Email" class="small-input" auto-complete="off"></el-input>
            </el-form-item>
            <el-form-item label="Status" :label-width="formLabelWidth">
              <el-switch
                v-model="userForm.Status"
                on-color="#13ce66"
                off-color="#ff4949"
                on-value="ACTIVE"
                off-value="INACTIVE">
              </el-switch>
            </el-form-item>
            <el-form-item label="Group" :label-width="formLabelWidth">
              <el-select v-model="userForm.Groups" placeholder="Select Group" multiple>
                <el-option
                  v-for="item in groupOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              <el-popover
                ref="popoverAddGroup"
                placement="top"
                width="300"
                @hide="handleGroupClose"
                v-model="visibleAddGroup">
                <el-form :model="boGroup">
                  <el-form-item label="GroupName" :label-width="formLabelWidth2">
                    <el-input v-model="boGroup.groupname"></el-input>
                  </el-form-item>
                </el-form>
                <div style="float: right">
                  <el-button size="mini" type="text" @click="visibleAddGroup = false">Cancel</el-button>
                  <el-button type="primary" size="mini" @click="handleGroupSubmit">Submit</el-button>
                </div>
              </el-popover>
              <el-button v-popover:popoverAddGroup type="info" style="margin-left:30px;">Add Group</el-button>
            </el-form-item>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <el-button @click="dialogUserFormVisible = false">cancel</el-button>
            <el-button :type="formButton.type" @click="handleSubmit" v-html="formButton.text "></el-button>
          </div>
        </el-dialog>
    </div>
</template>
<style>

</style>
<script>
    export default {
        created() {
            this.getList()
        },
        data(){
            return {
                list: [],
                currentPage: 1,
                pageSizes: [10, 20, 50, 100],
                pageSize: 10,
                count: 0,
                search: {
                    date: []
                },
                dialogUserFormVisible: false,
                userForm: {
                    UUID: '',
                    Status: 'ACTIVE',
                    Groups: []
                },
                formTitle: 'Add User',
                formButton: {
                    type: 'primary',
                    text: 'submit'
                },
                formLabelWidth: '120px',
                groupOptions: [],
                visibleAddGroup: false,
                boGroup: {

                },
                formLabelWidth2: '80px'
            }
        },
        methods: {
            getList(){
                var that = this
                window.axios.get('?action=user&func=list&page='+this.currentPage+'&size='+this.pageSize)
                    .then(function(response){
                      that.list = response.data.list
                      that.count = response.data.count
                    }).catch(function(error){

                    })
            },
            handleSearch(){
                alert('search')
            },
            handleSizeChange(size){
                this.pageSize = size
                this.getList()
            },
            handleCurrentPageChange(currentPage){
                this.currentPage = currentPage
                this.getList()
            },
            handleAdd(){
                this.dialogUserFormVisible = true
            },
            handleEdit(row){
              var that = this
              this.userForm = row
              this.$set(this.userForm, 'Groups', [])
              window.axios.get('?action=user&func=usergroup&useruuid='+this.userForm.UUID)
                .then(function(response){
                  that.userForm.Groups = response.data.list.map(item => {
                    return item.GroupUUID
                  })
                }).catch(function(error){

                })
                
                this.dialogUserFormVisible = true  
            },
            handleUserFormOpen(){
                var that = this
                window.axios.get('?action=group&func=list')
                    .then(function(response){
                      that.groupOptions = response.data.list.map(item => {
                        return {label: item.GroupName, value: item.UUID}
                      })
                    }).catch(function(error){

                    })
            },
            handleUserFormClose(){
                this.groupOptions = []
                this.userForm = {
                    UUID: '',
                    Status: 'ACTIVE',
                    Groups: []
                },
                this.formButton = {
                    type: 'primary',
                    text: 'submit'
                }
            },
            handleSubmit(){
                var that = this
                var uuid = this.userForm.UUID
                var formData = window.qs.stringify(this.userForm)
                var url = ''
                if(uuid.length > 0)
                {
                    // update
                    url = '?action=user&func=update'
                }else{
                    // add
                    url = '?action=user&func=insert'
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
                    message: '添加成功!'
                  })
                   that.dialogUserFormVisible = false;
                }).catch(function(error){
                  that.$message.error(error.response.data.content)
                  that.formButton = {
                    type: 'danger',
                    text: '重试'
                   }
                })
            },
            handleGroupClose() {

            },
            handleGroupSubmit() {
                var that = this
                var url = '?action=group&func=insert'
                var formData = window.qs.stringify(this.boGroup)
                window.axios.post(url, formData)
                    .then(function(response){
                        var groupOption = new Object()
                        groupOption.value  = response.data.content.UUID
                        groupOption.label  = response.data.content.GroupName

                        that.visibleAddGroup = false
                        that.groupOptions.push(groupOption)
                        that.userForm.Groups.push(groupOption.value)
                    }).catch(function(error){
                        that.$message.error(error.data.content)
                    })
            }
        }
    }
</script>