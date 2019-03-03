# phpcron项目

> php实现crontab功能

## 部署

### 环境要求
- 仅在unix环境下有效
- php >= 5.3, pcntl extension, redis extension, posix extension
- mysql >= 5.6
- redis-server

### 安装
执行 
```bash
sh ./install.sh
```

### crontab部署入口文件，监测文件以及报警文件
```bash
# phpcron 入口程序
* * * * * php /path/to/cron_controller.php >> /path/to/logs/crontab.txt 2>&1
# phpcron 报警程序
*/2 * * * * php /path/to/cron_alert.php >> /path/to/crontab_alert.txt 2>&1
# phpcron 自我检测程序
*/5 * * * * php /path/to/cron/cron_check.php >> /path/to/crontab_check.txt 2>&1
```


## 界面操作

### Cron模块
功能
1. 新增Cron
2. 修改Cron
3. 按条件查询Cron

### Cron Log模块
功能
1. 按条件查询Cron Log

### User Group模块
功能
1. 添加用户/组别
2. 修改用户/组别

## 更新

- 2017-07-09    v1.0

    1. phpcron可以实际调用脚本
    2. 有界面可以增删改查配置
    3. 有界面可以按照时间筛选查询log
    4. 脚本运行失败的时候有邮件报警

- 2017-07-14    v1.1
    
    1. 优化BoCrontab类，检测程序期望输出作为报警条件
    2. 给界面增加过滤条件
    3. Cron Log模块加入 轮询请求一下log list逻辑

- 2017-07-19    v1.2

    1. 优化BoAlert类，优化脚本运行失败邮件发送功能，控制邮件开始（前三次报错）即时发送，，后期延时（每隔一小时）发送
    测试：生成错误脚本，每五分钟运行。邮件发送脚本每两分钟运行
    结果：前三次平均五分钟发送一次，，间隔一小时后再次发送一次