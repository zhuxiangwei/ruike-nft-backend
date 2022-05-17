# PHP接口服务，提供前端调用

#### 介绍
{**以下是接口服务说明，您可以替换此简介**
本项目实现了商城的后端接口，使用PHP语言开发。
}

#### 软件架构
软件架构说明

1. MQTT消息队列服务，使用MQTT发送相关数据到指定主题，实现数据冗余、健康监控、日志记录
2. PHP+webman+MariaDB+Redis
3. ORM使用RedBeanPHP
4. 统一接口返回
    json格式{code:xxxx,message:xxx,data:xxxx}

    code: 200执行成功，300更新token，500错误

5. MQTT的PHP库使用 workerman/mqtt https://github.com/walkor/mqtt
6. MQTT测试服务端使用 Mosquitto https://mosquitto.org/，还是线上比较方便，linux自带
7. 使用phpqrcode生成二维码 http://phpqrcode.sourceforge.net/
8. 使用clicaptcha实现文字验证码

#### 安装教程

1. 创建webman空白项目，添加需要的插件
   composer create-project workerman/webman
   composer require -W illuminate/database illuminate/pagination illuminate/events
   composer require -W illuminate/redis
   composer require tinywan/storage

   阿里云短信sdk
   composer require alibabacloud/dysmsapi-20170525 2.0.9

2. 数据库脚本存放在doc目录下，文件名ruike.sql，mysql -u -p进入数据库控制台然后source ruike.sql导入
3. 前端接口服务，在webman目录下运行php start.php start -d，http端口12345
4. 后端辅助服务，直接运行php main.php start -d，ws端口23456

4. 如果需要mqtt测试服务器，可以启动 mosquitto，mosquitto -c /etc/mosquitto/mosquitto.conf -d，mosquitto.conf文件存放在doc目录下

5. 如果需要安装Mariadb
   sudo dnf install mariadb-server
   sudo systemctl enable mariadb
   sudo systemctl start mariadb
   sudo mysql_secure_installation

6. 如果需要安装Redis
   sudo dnf install redis
   sudo systemctl enable redis
   sudo systemctl start redis

7. 如果需要安装Nginx
   sudo dnf install nginx
   sudo systemctl enable nginx
   sudo systemctl start nginx

8. 如果需要安装PHP-FPM
   sudo dnf install php-fpm
   sudo systemctl enable php-fpm
   sudo systemctl start php-fpm

   附加的php扩展
   dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
   dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
   dnf module reset php
   dnf module enable php:remi-8.1

   sudo dnf install php-pdo php-gd php-mbstring php-mysqlnd php-ldap php-json php-xml php-zip php-process php-redis

   安装composer
   php -r "copy('https://install.phpcomposer.com/installer', 'composer-setup.php');"
   php composer-setup.php
   mv composer.phar /usr/local/bin/composer
   composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

#### 数据库设计
1. 使用Redis实现token防刷机制
2. 使用MariaDB实现整体业务接口的数据存储
3. MariaDB表结构设计原则，非必要不允许字段默认值为NULL，非必要不得违背设计范式，表名字段名非必要不追加修饰，禁止驼峰命名，全小写，下划线连接

#### 平台运行条件和参数
1. 海报的模板，背景模板文件需要放到public目录，并按照规定的文件名命名

#### 常见运营设置
1. 分类表
2. 海报表
3. 广告表


而开定制联系qq250822207