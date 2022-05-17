<?php
    header("Access-Control-Allow-Origin: http://localhost:80");
    header('Access-Control-Allow-Credentials: true');

    use AlibabaCloud\SDK\AliSMS\SMSRegist;
    require_once __DIR__ . '/smsregist.php';

    require('clicaptcha.class.php');

    $clicaptcha = new clicaptcha();

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    if(count($_GET) > 0) {
        if(array_key_exists('token', $_GET)) {
            $phone = $_GET['token'];
            if(strlen($phone) != 11 || false == is_numeric($phone)) {
                //手机号不对
                header('Clicaptcha-Response: invalid');
            } else {
                $redis->set('wordcode-' . $phone, time());
                header('Clicaptcha-Response: ok');
            }
        } else {
            header('Clicaptcha-Response: null');
        }
        $clicaptcha->creat();
        $redis->close();
        return;
    }

    if(count($_POST) > 0) {
        if(array_key_exists('do', $_POST) && array_key_exists('info', $_POST)) {
            if($_POST['do'] == 'check') {
                if($clicaptcha->check($_POST['info'], false)) {
                    if(array_key_exists('token', $_POST)) {
                        $phone = $_POST['token'];
                        if(strlen($phone) != 11 || false == is_numeric($phone)) {
                            //手机号不对
                            $redis->close();
                            die('1');
                        }
                        $timestamp = $redis->get('wordcode-' . $phone);
                        if(is_null($timestamp))
                        {
                            $timestamp = 0;
                        }
                        if(time() - $timestamp < 2) {
                            //点击最少需要两秒吧
                            $redis->close();
                            die('2');
                        }
                        $timestamp = $redis->get("clicaptcha-" . $phone);
                        if(is_null($timestamp))
                        {
                            $timestamp = 0;
                        }
                        if(time() - $timestamp < 300) {
                            //短信有效期五分钟
                            $redis->close();
                            die('3');
                        }
                        $code = rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                        $redis->set("clicaptcha-" . $phone, time());
                        $redis->set("smscode-" . $phone, $code);
                        SMSRegist::sendCode($phone, $code);
                        $redis->close();
                    }
                    die('0');
                } else {
                    $redis->close();
                    die('4');
                }
            } else {
                $redis->close();
                die('5');
            }
        } else {
            $redis->close();
            die('6');
        }
    } else {
        header('Clicaptcha-Response: null');
        $clicaptcha->creat();
        $redis->close();
        return;
    }
?>
