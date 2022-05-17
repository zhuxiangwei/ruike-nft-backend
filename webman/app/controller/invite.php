<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class invite
{
    //http://116.205.243.252:12345/invite/list
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

        $mine_obj = Db::table('user')->select('id')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $invite_array = Db::table('invite')->select('id', 'accept', 'gift', 'time')->where([
            ['uid', '=', $mine_obj->id]
        ])->get();

        if(is_null($invite_array))
        {
            return json(['code'=>406,'message'=>'invite_array is_null','data'=>array()]);
        }

        foreach($invite_array as $invite)
        {
            $invite_obj = Db::table('user')->select('id', 'uuid', 'name', 'nick', 'avatar')->where("id", $invite->accept)->get();
            if(is_null($invite_obj) == false)
            {
                $invite->accept = $invite_obj;
            }
            $gift_obj = Db::table('order')->select(
                'order.id AS order_id',
                'production.id AS production_id',
                'catalog.name AS catalog_name',
                'production.index AS production_index')
            ->leftJoin('production', 'order.pid', '=', 'production.id')
            ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
            ->where("order.id", $invite->gift)->get();
            if(is_null($gift_obj) == false)
            {
                $invite->gift = $gift_obj;
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>$invite_array]);
    }
}