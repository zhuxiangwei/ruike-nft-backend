<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\SDK\AliSMS;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\Tea\Tea;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;

require_once __DIR__ . '/vendor/autoload.php';

class SMSRegist {

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Dysmsapi Client
     */
    public static function createClient($accessKeyId, $accessKeySecret){
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "dysmsapi.aliyuncs.com";
        return new Dysmsapi($config);
    }

    /**
     * @param string[] $args
     * @return void
     */
    public static function sendCode($phone, $code) {
        $client = self::createClient("", "");
        $sendSmsRequest = new SendSmsRequest([
            "signName" => "",
            "templateCode" => "",
            "phoneNumbers" => $phone,
            "templateParam" => "{\"code\":\"$code\"}"
        ]);
        $resp = $client->sendSms($sendSmsRequest);
    }
}