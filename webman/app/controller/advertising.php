<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class advertising
{
    //http://116.205.243.252:12345/advertising/list
    //{"token":"","uuid":""}
    public function list(Request $request)
    {
        echo "data received from ip " . $request->getRemoteIp() . "\n";
        $data = $request->rawBody();
        var_dump($data);
        if(strlen($data) == 0)
        {
            return json(['code'=>500,'message'=>'rawBody strlen == 0','data'=>array()]);
        }
        $data_obj = json_decode($data, true);
        if(is_null($data_obj))
        {
            return json(['code'=>500,'message'=>'data_obj is_null','data'=>array()]);
        }
        if(false == isset($data_obj['token']))
        {
            return json(['code'=>500,'message'=>'isset token false','data'=>array()]);
        }
        if(false == isset($data_obj['uuid']))
        {
            return json(['code'=>500,'message'=>'isset uuid false','data'=>array()]);
        }

        $token = Redis::get("token-" . $data_obj['uuid']);
        if(is_null($token))
        {
            return json(['code'=>404,'message'=>'token is null','data'=>array()]);
        }

        if(strcmp($token, $data_obj['token']) != 0)
        {
            return json(['code'=>405,'message'=>'token is wrong','data'=>array()]);
        }

        if(false == Db::table('user')->where('uuid', $data_obj['uuid'])->exists())
        {
            return json(['code'=>500,'message'=>'user uuid not exists','data'=>array()]);
        }

        $ads_array = Db::table('advertising')->select('id', 'image', 'url')
        ->where([
            ['status', '=', 0]
        ])->get();

        if(is_null($ads_array))
        {
            return json(['code'=>406,'message'=>'ads_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$ads_array]);
    }
}
