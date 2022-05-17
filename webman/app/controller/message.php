<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class message
{
    //http://116.205.243.252:12345/message/list
    //{"token":"","uuid":"","type":0,"page":1}
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
        if(false == isset($data_obj['type']))
        {
            return json(['code'=>500,'message'=>'isset type false','data'=>array()]);
        }
        if(false == isset($data_obj['page']))
        {
            return json(['code'=>500,'message'=>'isset page false','data'=>array()]);
        }
        if($data_obj['page'] <= 0)
        {
            return json(['code'=>500,'message'=>'page <= 0','data'=>array()]);
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

        $mine_obj = Db::table('user')->select('id')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $message_array = Db::table('message')->select('id', 'from', 'to', 'content', 'time')->where([
            ['type', '= ', $data_obj['type']],
            ['to', '=', $mine_obj->id]
        ])->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($message_array))
        {
            return json(['code'=>406,'message'=>'message_array is_null','data'=>array()]);
        }

        foreach($message_array as $message)
        {
            if($data_obj['type'] == 0)
            {
                $from_obj = Db::table('user')->select('id', 'uuid', 'name', 'nick', 'avatar')->where("id", $message->from)->get();
                if(is_null($from_obj) == false)
                {
                    $message->from_user = $from_obj;
                }
            }
            $to_obj = Db::table('user')->select('id', 'uuid', 'name', 'nick', 'avatar')->where("id", $message->to)->get();
            if(is_null($to_obj) == false)
            {
                $message->to_user = $to_obj;
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>$message_array]);
    }
}