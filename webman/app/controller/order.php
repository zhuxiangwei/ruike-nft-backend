<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class order
{
    //http://116.205.243.252:12345/order/list
    //{"token":"","uuid":"","keyword":"","type":0,"page":1}
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

        $where_raw = " order.type=" . $data_obj['type'] . " AND order.buyer=" . $mine_obj->id . " ";

        if(true == isset($data_obj['keyword']))
        {
            if(strlen($data_obj['keyword']) > 0)
            {
                $where_raw .= " AND 0<>INSTR(production.name, '" . $data_obj['keyword'] . "') ";
            }
        }

        $order_array = Db::table('order')->select(
            'order.id AS order_id',
            'order.uuid AS order_uuid',
            'order.status AS order_status',
            'production.id AS production_id',
            'production.uuid AS production_uuid',
            'production.index AS production_index',
            'production.aid AS production_aid',
            'production.name AS production_name',
            'production.images AS production_images',
            'production.price AS production_price',
            'author.name AS author_name',
            'author.logo AS author_logo',
            'catalog.total AS catalog_total'
        )
        ->leftJoin('production', 'order.pid', '=', 'production.id')
        ->leftJoin('author', 'author.id', '=', 'production.aid')
        ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
        ->whereRaw($where_raw)->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($order_array))
        {
            return json(['code'=>406,'message'=>'order_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$order_array]);
    }

    //http://116.205.243.252:12345/order/fetch
    //{"token":"","uuid":"","oid":""}
    public function fetch(Request $request)
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
        if(false == isset($data_obj['oid']))
        {
            return json(['code'=>500,'message'=>'isset oid false','data'=>array()]);
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

        $order_array = Db::table('order')->select(
            'order.id AS order_id',
            'order.uuid AS order_uuid',
            'order.hash AS order_hash',
            'order.time AS order_time',
            'production.id AS production_id',
            'production.uuid AS production_uuid',
            'production.index AS production_index',
            'production.aid AS production_aid',
            'production.name AS production_name',
            'production.images AS production_images',
            'production.price AS production_price',
            'author.name AS author_name',
            'author.logo AS author_logo',
            'catalog.total AS catalog_total'
        )
        ->leftJoin('production', 'order.pid', '=', 'production.id')
        ->leftJoin('author', 'author.id', '=', 'production.aid')
        ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
        ->where("order.id", $data_obj['oid'])->get();

        if(is_null($order_array))
        {
            return json(['code'=>406,'message'=>'order_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$order_array]);
    }


    //http://116.205.243.252:12345/order/history
    //{"token":"","uuid":"","pid":""}
    public function history(Request $request)
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
        if(false == isset($data_obj['pid']))
        {
            return json(['code'=>500,'message'=>'isset pid false','data'=>array()]);
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

        $mine_obj = Db::table('user')->select('id','name')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $history_array = Db::table('order')->select(
            'order.id AS order_id',
            'order.seller AS order_seller',
            'order.buyer AS order_buyer',
            'order.uuid AS order_uuid',
            'order.type AS order_type',
            'order.hash AS order_hash',
            'order.price AS order_price',
            'order.status AS order_status',
            'order.time AS order_time',
            'user.uuid AS owner_uuid',
            'user.name AS owner_name',
            'user.avatar AS owner_avatar'
        )
        ->leftJoin('user', 'user.id', '=', 'order.buyer')
        ->where([
            ['order.pid', '=', $data_obj['pid']]
        ])->get();

        if(is_null($history_array))
        {
            return json(['code'=>406,'message'=>'history_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$history_array]);
    }
}