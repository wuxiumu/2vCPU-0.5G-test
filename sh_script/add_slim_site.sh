#!/bin/bash

# 用法: ./add_slim_site.sh www.aicaocai.com  --ssl[--ssl]

DOMAIN=$1
ENABLE_SSL=$2
WEBROOT="/var/www/$DOMAIN"
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"

if [[ -z "$DOMAIN" ]]; then
    echo "❌ 请输入域名参数，如: ./add_slim_site.sh slim.aicaocai.com [--ssl]"
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
