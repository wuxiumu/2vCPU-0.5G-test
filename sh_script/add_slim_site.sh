#!/bin/bash

# 用法: ./add_slim_site.sh aicaocai.com  --ssl

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

# 2. 生成 index.html
cat <<EOF | sudo tee $WEBROOT/public/index.html > /dev/null
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>$DOMAIN</title>
  <style>
    body { margin:0; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#f8fafc; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Helvetica Neue",Arial,sans-serif;}
    .logo { display:block; margin:0 auto 26px auto; max-width:180px; width:55vw;}
    .title { font-size:2rem; font-weight:700; color:#222; text-align:center; letter-spacing:.03em;}
  </style>
</head>
<body>
  <img class="logo" src="https://img20.360buyimg.com/openfeedback/jfs/t1/328719/36/17066/56766/68bda4d9F886063fc/1d01b115131a1a7e.png" alt="logo" />
  <div class="title">$DOMAIN</div>
</body>
</html>
EOF

# 3. 创建 Nginx 配置文件
cat <<EOF | sudo tee $NGINX_CONF > /dev/null
server {
    listen 80;
    server_name $DOMAIN;

    root $WEBROOT/public;
    index index.html index.php;

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|svg|ico)$ {
        expires 7d;
        add_header Cache-Control "public";
    }

    location / {
        try_files \$uri \$uri/ /index.html;
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
