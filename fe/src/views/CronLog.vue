<template>
    <div id="cron-log">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
          <el-breadcrumb-item>CronLog</el-breadcrumb-item>
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
        v-loading.body="loading"
        :row-class-name="setTableRowClassName"
        >
         <el-table-column
            prop="Name"
            label="Name">
          </el-table-column>
          <el-table-column
            prop="CommandString"
            label="CommandString">
          </el-table-column>
          <el-table-column
            prop="ProcessStatus"
            label="Status"
            width="100">
          </el-table-column>
          <el-table-column
            prop="StartTime"
            label="StartTime"
            width="200">
          </el-table-column>
          <el-table-column
            prop="EndTime"
            label="EndTime"
            width="200">
          </el-table-column>
          <el-table-column
            prop="ProcessId"
            label="PId"
            width="100">
          </el-table-column>
          <el-table-column
            prop="UserName"
            label="Excute User">
          </el-table-column>
          <!-- <el-table-column
            label="Operation">
              <template scope="scope">
                <el-button type="info">View Log</el-button>
              </template>
          </el-table-column> -->
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
    </div>
</template>
<style>
  
</style>
<script>
    export default {
        created(){
           var now_day = new Date()
           now_day = new Date(now_day.getTime() + 3600 * 1000 * 24 * 3)
           var yes_day = new Date(now_day.getTime() - 3600 * 1000 * 24 * 6)
           this.search.date = [yes_day, now_day]
           this.getList()
           var that = this
           var id = setInterval(function(){
            that.getList()
            if(window.location.href.indexOf('cron-log') == -1)
              clearInterval(id)
           }, 60000);
        },
        data() {
            return {
                list: [],
                currentPage: 1,
                pageSizes: [10, 20, 50, 100],
                pageSize: 10,
                count: 0,
                search: {
                    date: [],
                    status: '',
                    name: '',
                    commandstring: '',
                },
                dateOptions: {
                  shortcuts: [{
                    text: '最近一周',
                    onClick(picker) {
                      const end = new Date();
                      const start = new Date();
                      start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                      picker.$emit('pick', [start, end]);
                    }
                  }, {
                    text: '最近一个月',
                    onClick(picker) {
                      const end = new Date();
                      const start = new Date();
                      start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                      picker.$emit('pick', [start, end]);
                    }
                  }, {
                    text: '最近三个月',
                    onClick(picker) {
                      const end = new Date();
                      const start = new Date();
                      start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                      picker.$emit('pick', [start, end]);
                    }
                  }]
                },
                processStatusOptions: [
                  {
                    label: 'RUNNING',
                    value: 'RUNNING'
                  },
                  {
                    label: 'FAILED',
                    value: 'FAILED'
                  },
                  {
                    label: 'SUCC',
                    value: 'SUCC'
                  },
                  {
                    label: 'LOST',
                    value: 'LOST'
                  }
                ],
                loading: false
            }
        },
        methods: {
             getList() {
              var that = this
              var start_str,end_str,status_str,commandstring_str, name_str = ''
              if(this.search.date.length >0)
              {
                var dataRange = this.search.date
                var starttime = this.formatDate(dataRange[0])
                var endtime = this.formatDate(dataRange[1])
                if(starttime.length >0)    start_str = '&starttime='+starttime
                if(endtime.length >0)    end_str = '&endtime='+endtime
              }
              if(this.search.status.length > 0 || this.search.status != undefined) status_str = "&status="+this.search.status
              if(this.search.commandstring.length > 0 || this.search.commandstring != undefined) commandstring_str = "&commandstring="+this.search.commandstring
              if(this.search.name.length > 0 || this.search.name != undefined) name_str = "&name="+this.search.name
              this.loading = true;
              window.axios.get('?action=cron_log&func=list&page='+this.currentPage+'&size='+this.pageSize+start_str+end_str+status_str+commandstring_str+name_str)
                .then(function(response){
                  that.list = response.data.list ? response.data.list: []
                  that.count = response.data.count
                  that.loading = false
                  that.message({
                    type: 'success',
                    message: '数据接收成功!'
                  })

                }).catch(function(error){

                })
                this.loading = false
            },
             handleSizeChange(size) {
              this.pageSize = size
              this.getList()
            },
            handleCurrentPageChange(currentPage) {
              this.currentPage = currentPage
              this.getList()
            },
            handleSearch() {
                this.getList();
            },
            formatDate(datetime) {
                var date = new Date(datetime)
                return date.getFullYear()+'-'+(date.getMonth() +1)+'-'+date.getDate()+ ' '+ date.getHours()+':'+date.getMinutes()+':'+date.getSeconds()
            },
            setTableRowClassName(row, index) {
              if(row.ProcessStatus == 'RUNNING')
              {
                this.$notify.info({
                  title: 'Command RUNNING',
                  message: row.Name+' is RUNNING'
                })
                return 'running-row'
              }else if(row.ProcessStatus == 'FAILED')
              {
                this.$notify.error({
                  title: 'Command Failed!',
                  message: row.Name+' is Failed!'
                })
                return 'failed-row'
              }
            }
        }
    }
</script>