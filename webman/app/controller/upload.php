<?php

namespace app\controller;

use support\Request;
use support\Redis;
use Tinywan\Storage\Storage;

class upload
{
    //http://116.205.243.252:12345/upload/local
    public function local(Request $request)
    {
        $token = $request->post('token', '');
        $uuid = $request->post('uuid', '');
        if(strlen($token) <= 0 || strlen($uuid) <= 0)
        {
            return json(['code'=>500,'message'=>'token or uuid is null','data'=>array()]);
        }

        $token_local = Redis::get("token-" . $uuid);
        if(is_null($token_local))
        {
            return json(['code'=>404,'message'=>'token is null','data'=>array()]);
        }

        if(strcmp($token, $token_local) != 0)
        {
            return json(['code'=>405,'message'=>'token is wrong','data'=>array()]);
        }

        Storage::config();
        $res = Storage::uploadFile();
        if(is_null($res))
        {
            return json(['code'=>407,'message'=>'res is null','data'=>array()]);
        }
        if(count($res) == 0)
        {
            return json(['code'=>407,'message'=>'res count 0','data'=>array()]);
        }
        $url_array = array();
        foreach($res as $file_obj)
        {
            $url_array[] = $file_obj['url'];
        }
        if(count($url_array) == 0)
        {
            return json(['code'=>407,'message'=>'res count 0','data'=>array()]);
        }
        return json(['code'=>200,'message'=>'ok','data'=>$url_array]);
    }
}