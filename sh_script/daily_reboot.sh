#!/bin/bash
/sbin/reboot
#0 4 * * * /sbin/reboot >> /var/log/daily_reboot.log 2>&1
#验证配置是否生效
#查看当前定时任务：
#bash
#crontab -l
#
#
#（如果是方法 2，查看/etc/cron.d/daily_reboot文件）
#检查cron服务状态：
#bash
## 对于systemd系统（Ubuntu 16.04+、CentOS 7+）
#sudo systemctl status cron
#
## 对于sysvinit系统
#sudo service cron status
#
#
#
#如果未运行，启动服务：
#bash
#sudo systemctl start cron  # 或 sudo service cron start