<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class favorite
{
    //http://116.205.243.252:12345/favorite/author
    //{"token":"","uuid":"","aid":"","action":1}
    public function author(Request $request)
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
        if(false == isset($data_obj['aid']))
        {
            return json(['code'=>500,'message'=>'isset aid false','data'=>array()]);
        }
        if(false == isset($data_obj['action']))
        {
            return json(['code'=>500,'message'=>'isset action false','data'=>array()]);
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

        $mine_obj = Db::table('user')->select('id')
        ->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();

        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $author_obj = Db::table('author')->select('id')
        ->where([
            ['id', '=', $data_obj['aid']]
        ])->limit(1)->first();

        if(is_null($author_obj))
        {
            return json(['code'=>500,'message'=>'author_obj is_null','data'=>array()]);
        }

        if($data_obj['action'] == 0)
        {
            Db::table('favorite')->where([
                ['uid', '=', $mine_obj->id],
                ['aid', '=', $data_obj['aid']],
                ['type', '=', 1]
            ])->delete();
        }
        if($data_obj['action'] == 1)
        {
            Db::table('favorite')->insert([
                'uid' => $mine_obj->id,
                'aid' => $data_obj['aid'],
                'type' => 1
            ]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/favorite/production
    //{"token":"","uuid":"","pid":"","action":1}
    public function production(Request $request)
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
        if(false == isset($data_obj['action']))
        {
            return json(['code'=>500,'message'=>'isset action false','data'=>array()]);
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

        $mine_obj = Db::table('user')->select('id')
        ->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();

        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $production_obj = Db::table('production')->select('id')
        ->where([
            ['id', '=', $data_obj['pid']]
        ])->limit(1)->first();

        if(is_null($production_obj))
        {
            return json(['code'=>500,'message'=>'production_obj is_null','data'=>array()]);
        }

        if($data_obj['action'] == 0)
        {
            Db::table('favorite')->where([
                ['uid', '=', $mine_obj->id],
                ['pid', '=', $data_obj['pid']],
                ['type', '=', 0]
            ])->delete();
        }

        if($data_obj['action'] == 1)
        {
            Db::table('favorite')->insert([
                'uid' => $mine_obj->id,
                'pid' => $data_obj['pid'],
                'type' => 0
            ]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/favorite/list
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

        $favorite_array = Db::table('favorite')->select('aid', 'pid')->where([["type", '=', $data_obj['type']],['uid', '=', $mine_obj->id]])->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($favorite_array))
        {
            return json(['code'=>406,'message'=>'favorite_array is_null','data'=>array()]);
        }

        foreach($favorite_array as $favorite_obj)
        {
            if($data_obj['type'] == 0)
            {
                $favorite_obj->data = Db::table('production')->select(
                    'production.uuid AS production_uuid',
                    'production.name AS production_name',
                    'production.price AS production_price',
                    'production.images AS production_images',
                    'author.name AS author_name',
                    'user.avatar AS user_avatar')
                ->leftJoin('author', 'author.id', '=', 'production.aid')
                ->leftJoin('user', 'user.id', '=', 'author.uid')
                ->where([
                    ['production.id', '=', $favorite_obj->pid]
                ])->limit(1)->first();
            }
            else if($data_obj['type'] == 1)
            {
                $favorite_obj->data = Db::table('author')->select(
                    'user.uuid AS user_uuid',
                    'user.avatar AS user_avatar',
                    'author.name AS author_name')
                ->leftJoin('user', 'user.id', '=', 'author.uid')
                ->where([
                    ['author.id', '=', $favorite_obj->aid]
                ])->limit(1)->first();
                if(is_null($favorite_obj->data) == false)
                {
                    $cid_array = Db::table('catalog')->select('id')
                    ->where([
                        ['catalog.aid', '=', $favorite_obj->aid]
                    ])->get();
                    if(is_null($cid_array) == false)
                    {
                        $favorite_obj->data->count = count($cid_array);
                    }
                }
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>$favorite_array]);
    }
}