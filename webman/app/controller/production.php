<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class production
{
    //http://116.205.243.252:12345/production/list
    //{"token":"","uuid":"","keyword":"","cid":"","onsale":"","trend":"","page":1}
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
        if(false == isset($data_obj['cid']))
        {
            return json(['code'=>500,'message'=>'isset cid false','data'=>array()]);
        }
        if(false == isset($data_obj['onsale']))
        {
            return json(['code'=>500,'message'=>'isset onsale false','data'=>array()]);
        }
        if(false == isset($data_obj['trend']))
        {
            return json(['code'=>500,'message'=>'isset trend false','data'=>array()]);
        }
        if(strcmp($data_obj['trend'], 'asc') != 0 && strcmp($data_obj['trend'], 'desc') != 0)
        {
            return json(['code'=>500,'message'=>'trend value wrong','data'=>array()]);
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

        if(false == Db::table('user')->where('uuid', $data_obj['uuid'])->exists())
        {
            return json(['code'=>500,'message'=>'user uuid not exists','data'=>array()]);
        }

        if(strcmp($token, $data_obj['token']) != 0)
        {
            return json(['code'=>405,'message'=>'token is wrong','data'=>array()]);
        }

        $orderby_raw = " production.id ";

        if(strcmp($data_obj['trend'], "asc") == 0)
        {
            $orderby_raw .= " asc ";
        }
        if(strcmp($data_obj['trend'], "desc") == 0)
        {
            $orderby_raw .= " desc ";
        }

        $where_raw = " production.cid = " . $data_obj['cid'] . " AND production.onsale = " . $data_obj['onsale'];

        if(true == isset($data_obj['keyword']))
        {
            if(strlen($data_obj['keyword']) > 0)
            {
                $where_raw .= " AND 0<>INSTR(production.name, '" . $data_obj['keyword'] . "') ";
            }
        }

        $production_array = Db::table('production')->select(
            'production.id AS production_id',
            'production.aid AS production_aid',
            'production.name AS production_name',
            'production.index AS production_index',
            'production.images AS production_images',
            'production.price AS production_price',
            'production.browse AS production_browse',
            'production.onsale AS production_onsale',
            'author.name AS author_name',
            'author.logo AS author_logo',
            'user.name AS owner_name',
            'user.avatar AS owner_avatar',
            'catalog.total AS catalog_total'
        )
        ->leftJoin('author', 'author.id', '=', 'production.aid')
        ->leftJoin('user', 'user.id', '=', 'production.oid')
        ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
        ->whereRaw($where_raw)->orderByRaw($orderby_raw)->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($production_array))
        {
            return json(['code'=>406,'message'=>'production_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$production_array]);
    }

    //http://116.205.243.252:12345/production/fetch
    //{"token":"","uuid":"","pid":""}
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

        $mine_obj = Db::table('user')->select('id')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $production_obj = Db::table('production')->select(
            'production.uuid AS production_uuid',
            'production.name AS production_name',
            'production.index AS production_index',
            'production.description AS production_description',
            'production.images AS production_images',
            'production.bcid AS production_bcid',
            'production.url AS production_url',
            'production.price AS production_price',
            'production.browse AS production_browse',
            'production.onsale AS production_onsale',
            'production.time AS production_time',
            'author.id AS author_id',
            'author.name AS author_name',
            'author.description AS author_description',
            'author.logo AS author_logo',
            'user.id AS owner_id',
            'user.name AS owner_name',
            'user.avatar AS owner_avatar',
            'catalog.total AS catalog_total',
            'catalog.single AS catalog_single'
        )
        ->leftJoin('author', 'author.id', '=', 'production.aid')
        ->leftJoin('user', 'user.id', '=', 'production.oid')
        ->leftJoin('catalog', 'catalog.id', '=', 'production.cid')
        ->where([
            ['production.id', '=', $data_obj['pid']]
        ])->limit(1)->first();

        if(is_null($production_obj))
        {
            return json(['code'=>406,'message'=>'production_obj is_null','data'=>array()]);
        }

        $production_obj->favorite = Db::table('favorite')->where([['pid', '=', $data_obj['pid']], ['uid', '=', $mine_obj->id]])->exists() ? 1 : 0;

        $production_obj->indictment = Db::table('indictment')->where('pid', $data_obj['pid'])->count();

        return json(['code'=>200,'message'=>'ok','data'=>$production_obj]);
    }

    //http://116.205.243.252:12345/production/browse
    //{"token":"","uuid":"","pid":""}
    public function browse(Request $request)
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

        if(false == Db::table('user')->where('uuid', $data_obj['uuid'])->exists())
        {
            return json(['code'=>500,'message'=>'user uuid not exists','data'=>array()]);
        }

        if(false == Db::table('production')->where('id', $data_obj['pid'])->exists())
        {
            return json(['code'=>500,'message'=>'production pid not exists','data'=>array()]);
        }

        Db::table('production')->where('id', $data_obj['pid'])->increment('browse');

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/production/indictment
    //{"token":"","uuid":"","pid":"","type":"","note":"","images":"","contact":""}
    public function indictment(Request $request)
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
        if(false == isset($data_obj['type']))
        {
            return json(['code'=>500,'message'=>'isset type false','data'=>array()]);
        }
        if(false == isset($data_obj['note']))
        {
            return json(['code'=>500,'message'=>'isset note false','data'=>array()]);
        }
        if(false == isset($data_obj['images']))
        {
            return json(['code'=>500,'message'=>'isset images false','data'=>array()]);
        }
        if(false == isset($data_obj['contact']))
        {
            return json(['code'=>500,'message'=>'isset contact false','data'=>array()]);
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

        if(false == Db::table('production')->where('id', $data_obj['pid'])->exists())
        {
            return json(['code'=>500,'message'=>'production not exists','data'=>array()]);
        }

        Db::table('indictment')->insert([
            'uid' => $mine_obj->id,
            'pid' => $data_obj['pid'],
            'type' => $data_obj['type'],
            'note' => $data_obj['note'],
            'images' => $data_obj['images'],
            'contact' => $data_obj['contact']
        ]);

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/production/present
    //{"token":"","uuid":"","code":"","pid":"","receiver":""}
    public function present(Request $request)
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
        if(false == isset($data_obj['code']))
        {
            return json(['code'=>500,'message'=>'isset code false','data'=>array()]);
        }
        if(false == isset($data_obj['pid']))
        {
            return json(['code'=>500,'message'=>'isset pid false','data'=>array()]);
        }
        if(false == isset($data_obj['receiver']))
        {
            return json(['code'=>500,'message'=>'isset receiver false','data'=>array()]);
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

        $mine_obj = Db::table('user')->select('id','nick','name')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $timestamp = Redis::get("clicaptcha-" . $mine_obj->name);
        if(is_null($timestamp))
        {
            $timestamp = 0;
        }
        if(time() - $timestamp > 300)
        {
            return json(['code'=>400,'message'=>'code is expired','data'=>array()]);
        }
        $code = Redis::get("smscode-" . $mine_obj->name);
        if(is_null($code))
        {
            $code = "";
        }
        if((strcmp($code, $data_obj['code']) != 0) || strlen($code) == 0)
        {
            return json(['code'=>401,'message'=>'code is wrong','data'=>array()]);
        }

        $production_obj = Db::table('production')->select('id','name')->where([
            ['id', '=', $data_obj['pid']],
            ['oid', '=', $mine_obj->id]
        ])->limit(1)->first();
        if(is_null($production_obj))
        {
            return json(['code'=>500,'message'=>'production_obj is_null','data'=>array()]);
        }

        $ownership_obj = Db::table('ownership')->select('id')->where([
            ['uid', '=', $mine_obj->id],
            ['pid', '=', $data_obj['pid']],
            ['type', '=', 0]
        ])->limit(1)->first();
        if(is_null($ownership_obj))
        {
            return json(['code'=>500,'message'=>'ownership_obj is_null','data'=>array()]);
        }

        $user_obj = Db::table('user')->select('id')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['receiver']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        Db::table('production')->where([
            ['id', '=', $production_obj->id]
        ])->update(['oid' => $user_obj->id]);

        Db::table('ownership')->where([
            ['id', '=', $ownership_obj->id]
        ])->update(['type' => 1]);

        Db::table('ownership')->insert([
            'uid' => $user_obj->id,
            'pid' => $data_obj['pid']
        ]);

        Db::table('order')->insert([
            'seller' => $mine_obj->id,
            'buyer' => $user_obj->id,
            'pid' => $data_obj['pid'],
            'type' => 1
        ]);

        if(strlen($mine_obj->nick) > 0)
        {
            Db::table('message')->insert([
                'from' => $mine_obj->id,
                'to' => $user_obj->id,
                'content' => $mine_obj->nick . '赠送' . $production_obj->name . '给你了'
            ]);
        }
        else
        {
            Db::table('message')->insert([
                'from' => $mine_obj->id,
                'to' => $user_obj->id,
                'content' => $mine_obj->name . '赠送' . $production_obj->name . '给你了'
            ]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/production/purchase
    //{"token":"","uuid":"","code":"","pid":"","price":""}
    public function purchase(Request $request)
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
        if(false == isset($data_obj['pwd']))
        {
            return json(['code'=>500,'message'=>'isset pwd false','data'=>array()]);
        }
        if(false == isset($data_obj['pid']))
        {
            return json(['code'=>500,'message'=>'isset pid false','data'=>array()]);
        }
        if(false == isset($data_obj['price']))
        {
            return json(['code'=>500,'message'=>'isset price false','data'=>array()]);
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

        $timestamp = Redis::get("clicaptcha-" . $mine_obj->name);
        if(is_null($timestamp))
        {
            $timestamp = 0;
        }
        if(time() - $timestamp > 300)
        {
            return json(['code'=>400,'message'=>'code is expired','data'=>array()]);
        }
        $code = Redis::get("smscode-" . $mine_obj->name);
        if(is_null($code))
        {
            $code = "";
        }
        if((strcmp($code, $data_obj['code']) != 0) || strlen($code) == 0)
        {
            return json(['code'=>401,'message'=>'code is wrong','data'=>array()]);
        }

        $production_obj = Db::table('production')->select('id','oid','name','price')->where([
            ['id', '=', $data_obj['pid']],
            ['onsale', '=', '0']
        ])->limit(1)->first();
        if(is_null($production_obj))
        {
            return json(['code'=>500,'message'=>'production_obj is_null','data'=>array()]);
        }

        if($production_obj->price > $data_obj['price'])
        {
            return json(['code'=>408,'message'=>'price is wrong','data'=>array()]);
        }

        $order_obj = Db::table('order')->select('id')->where([
            'pid', '=', $data_obj['pid'],
            ['type', '=', '0'],
            ['status', '<>', '2']
        ])->limit(1)->first();
        if(is_null($order_obj) == false)
        {
            return json(['code'=>409,'message'=>'order_obj exists','data'=>array()]);
        }

        Db::table('order')->insert([
            'seller' => $production_obj->oid,
            'buyer' => $mine_obj->id,
            'pid' => $data_obj['pid'],
            'price' => $data_obj['price']
        ]);

        $order_obj = Db::table('order')->select('id','uuid','pid','seller','buyer','price','status','time')->where([
            'seller', '=', $production_obj->id,
            'buyer', '=', $mine_obj->id,
            'pid', '=', $data_obj['pid'],
            'price', '=', $data_obj['price']
        ])->limit(1)->first();

        if(is_null($order_obj))
        {
            return json(['code'=>410,'message'=>'purchase fail','data'=>array()]);
        }

        Db::table('message')->insert([
            'to' => $mine_obj->id,
            'type' => 1,
            'content' => $production_obj->name . '下单成功'
        ]);

        return json(['code'=>200,'message'=>'ok','data'=>$order_obj]);
    }

    //http://116.205.243.252:12345/production/paid
    //{"uuid":""}
    public function paid(Request $request)
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

        $order_obj = Db::table('order')->select('id','pid','seller','buyer')->where([
            'uuid', '=', $data_obj['uuid'],
            ['status', '=', '0']
        ])->limit(1)->first();
        if(is_null($order_obj) == false)
        {
            return json(['code'=>411,'message'=>'paid fail','data'=>array()]);
        }

        $production_obj = Db::table('production')->select('id','oid','name','price')->where([
            ['id', '=', $order_obj->pid],
            ['onsale', '=', '0']
        ])->limit(1)->first();
        if(is_null($production_obj))
        {
            return json(['code'=>500,'message'=>'production_obj is_null','data'=>array()]);
        }

        Db::table('production')->where([
            ['id', '=', $order_obj->pid]
        ])->update(['oid' => $order_obj->buyer]);

        Db::table('production')->where([
            ['id', '=', $order_obj->pid]
        ])->update(['onsale' => 1]);

        Db::table('ownership')->where([
            ['uid' => $order_obj->seller],
            ['pid', '=', $order_obj->pid],
            ['type', '=', 0],
        ])->update(['type' => 2]);

        Db::table('ownership')->insert([
            'uid' => $order_obj->buyer,
            'pid' => $order_obj->pid
        ]);

        Db::table('order')->where([
            ['id', '=', $order_obj->id]
        ])->update(['status' => 1]);

        Db::table('message')->insert([
            'to' => $order_obj->buyer,
            'type' => 1,
            'content' => $production_obj->name . '支付成功'
        ]);

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }
}