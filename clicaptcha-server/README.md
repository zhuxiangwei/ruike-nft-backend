# clicaptcha-server
中文点击验证码 PHP 后端支持，需搭配 [vue-clicaptcha](https://github.com/hooray/vue-clicaptcha) 一起使用。

## 使用

打开 clicaptcha.php

```php
header("Access-Control-Allow-Origin: http://localhost:8080");  // 如果后端和前端部署在不同域名下，则需要配置前端域名，不能使用 * 通配符
header('Access-Control-Allow-Credentials: true');

require('clicaptcha.class.php');

$clicaptcha = new clicaptcha();
if($_POST['do'] == 'check'){
	echo $clicaptcha->check($_POST['info'], false) ? 1 : 0;
}else{
	$clicaptcha->creat();
}
```