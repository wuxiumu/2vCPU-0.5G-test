# 2vCPU-0.5G-test
一个低配服务器测试脚本

##  2核0.5G 轻量级服务器

> 我想部署php7.4 + nginx 相关 的服务。推荐最小版本的服务，给我安装步骤

在你这台 **阿里云 2核 0.5GB 内存** 的轻量服务器上，部署 PHP 7.4 + Nginx 的确要精打细算。我们追求的是：


- **内存占用最小**
- **依赖精简**
- **服务稳定运行**
- **适合单站点或静态/小型 PHP 应用**





------





### **✅ 推荐组合**



| **服务** | **推荐版本**             | **说明**                               |
| -------- | ------------------------ | -------------------------------------- |
| PHP      | 7.4.x                    | 官方已停止维护，但兼容性高、内存占用低 |
| Nginx    | 1.18+ (稳定版)           | 稳定可靠，占用小                       |
| PHP-FPM  | PHP 自带                 | PHP FastCGI 管理器                     |
| OS       | Debian 11 / Ubuntu 20.04 | 系统包管理好，社区活跃                 |

> ⚠️ **不建议用 Apache**，因为它比 Nginx 更吃内存。



------





### **🧱 安装环境准备**







#### **1.**

#### **系统最小优化（Ubuntu/Debian）**



```
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget vim unzip net-tools software-properties-common
```



#### **2.**

#### **安装 Nginx**



```
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```



#### **3.**

#### **安装 PHP 7.4 + PHP-FPM**





> Ubuntu 默认源可能没有 PHP 7.4，需要加个 PPA（如果是 Debian，使用 sury 源）





##### **Ubuntu:**



```
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php7.4 php7.4-fpm php7.4-cli php7.4-mbstring php7.4-xml php7.4-curl php7.4-mysql php7.4-zip
```



##### **Debian:**



```
sudo apt install -y lsb-release apt-transport-https ca-certificates
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
sudo apt update
sudo apt install -y php7.4 php7.4-fpm php7.4-cli php7.4-mbstring php7.4-xml php7.4-curl php7.4-mysql php7.4-zip
```



#### **4.**

#### **启动 PHP-FPM**



```
sudo systemctl enable php7.4-fpm
sudo systemctl start php7.4-fpm
```



------





### **⚙️ 配置 Nginx 与 PHP-FPM**







#### **示例配置**

#### **/etc/nginx/sites-available/default**



```
server {
    listen 80;
    server_name localhost;

    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

> 重启服务：

```
sudo systemctl reload nginx
```



------





### **🔍 测试 PHP 环境**



```
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

访问你的公网 IP 地址（例：http://x.x.x.x/info.php）验证。



------





### **📉 内存优化建议（非常关键）**





- 关闭不必要服务：



```
sudo systemctl disable --now apache2
sudo systemctl disable --now snapd
```



- 精简 PHP 模块（只装你需要的）
- 调整 php-fpm 配置 /etc/php/7.4/fpm/pool.d/www.conf，例如：



```
pm = static
pm.max_children = 2
```



- Nginx 配置减少 worker：



```
worker_processes 1;
events {
    worker_connections 128;
}
```



------





### **🧩 可选（后期可加）**



| **功能**   | **推荐服务**      | **安装建议**         |
| ---------- | ----------------- | -------------------- |
| MySQL      | 使用远程或 SQLite | 本地占内存大，不推荐 |
| Redis      | 慎用              | 可加，但仅作缓存服务 |
| Supervisor | 可选              | 保活工具（守护进程） |



------





### **📦 运行简单 PHP 项目建议**





- 尽量使用 **原生 PHP 或轻量框架（如 Slim / Lumen）**
- 静态资源交给 Nginx 处理
- 不跑大并发或队列服务
- 日志及时清理，避免磁盘爆满





------



如你有更详细用途，比如要跑某个框架或管理系统（如 WordPress、Typecho），可以继续告诉我，我再帮你配套优化建议。

> 能不能写一个脚本，实现，添加域名的同时，创建目录。选择是否创建https的跳转

当然可以！我们来写一个 💡 **自动化脚本**，帮你：



> ✅ 添加 Nginx 虚拟主机

> ✅ 创建项目目录（含 public/index.php）

> ✅ 可选启用 HTTPS + 自动跳转（Let’s Encrypt）



------





## **🛠️ 一键建站脚本**

## **add_slim_site.sh**



```
#!/bin/bash

# 用法: ./add_slim_site.sh domain.com [--ssl]

DOMAIN=$1
ENABLE_SSL=$2
WEBROOT="/var/www/$DOMAIN"
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"

if [[ -z "$DOMAIN" ]]; then
    echo "❌ 请输入域名参数，如: ./add_slim_site.sh slim.51chigua.com [--ssl]"
    exit 1
fi

echo "🚀 开始为 $DOMAIN 创建站点..."

# 1. 创建站点目录
sudo mkdir -p $WEBROOT/public
sudo chown -R www-data:www-data $WEBROOT

# 2. 生成 index.php
cat <<EOF | sudo tee $WEBROOT/public/index.php > /dev/null
<?php
echo "Hello from $DOMAIN!";
EOF

# 3. 创建 Nginx 配置文件
cat <<EOF | sudo tee $NGINX_CONF > /dev/null
server {
    listen 80;
    server_name $DOMAIN;

    root $WEBROOT/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# 4. 启用站点
sudo ln -sf $NGINX_CONF /etc/nginx/sites-enabled/

# 5. 重载 nginx
sudo nginx -t && sudo systemctl reload nginx

# 6. 如果开启了 --ssl
if [[ "$ENABLE_SSL" == "--ssl" ]]; then
    echo "🔐 开始为 $DOMAIN 配置 HTTPS..."
    sudo apt install -y certbot python3-certbot-nginx
    sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m admin@$DOMAIN --redirect
fi

echo "✅ 站点 $DOMAIN 已创建成功！"
echo "📁 项目路径: $WEBROOT"
[[ "$ENABLE_SSL" == "--ssl" ]] && echo "🌐 访问地址: https://$DOMAIN" || echo "🌐 访问地址: http://$DOMAIN"
```



------





## **🔧 使用方式**





1. 保存为脚本文件：



```
nano add_slim_site.sh
chmod +x add_slim_site.sh
```



1. 创建站点：







- 不加 SSL：



```
./add_slim_site.sh slim.51chigua.com
```



- 自动加 HTTPS + 跳转：



```
./add_slim_site.sh slim.51chigua.com --ssl
```



------





## **✅ 脚本特点**





- 自动创建目录 /var/www/yourdomain
- 自动生成 index.php 页面
- 自动配置 Nginx
- 自动签发 HTTPS（可选）
- 自动重载服务





------



如果你想后续支持更多内容（如 Slim Skeleton 初始化、PHP 版本切换、反向代理等），我可以帮你升级为增强版 CLI 工具 😎
