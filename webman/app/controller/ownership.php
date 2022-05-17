<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class ownership
{
    //http://116.205.243.252:12345/ownership/list
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

        $mine_obj = Db::table('user')->select('id','name')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $where_raw = " ownership.type=" . $data_obj['type'] . " AND ownership.uid=" . $mine_obj->id;

        if(true == isset($data_obj['keyword']))
        {
            if(strlen($data_obj['keyword']) > 0)
            {
                $where_raw .= " AND 0<>INSTR(production.name, '" . $data_obj['keyword'] . "') ";
            }
        }

        $production_array = Db::table('ownership')->select(
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
        ->leftJoin('production', 'ownership.pid', '=', 'production.id')
        ->leftJoin('author', 'author.id', '=', 'production.aid')
        ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
        ->whereRaw($where_raw)->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($production_array))
        {
            return json(['code'=>406,'message'=>'production_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$production_array]);
    }
}