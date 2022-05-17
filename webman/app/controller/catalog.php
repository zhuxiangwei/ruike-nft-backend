<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class catalog
{
    //http://116.205.243.252:12345/catalog/author
    //{"token":"","uuid":""}
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

        $author_array = Db::table('author')->select('id', 'name')->get();

        if(is_null($author_array))
        {
            return json(['code'=>406,'message'=>'author_array is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$author_array]);
    }

    //http://116.205.243.252:12345/catalog/list
    //{"token":"","uuid":"","keyword":"","condition":"","category":"","aid":"","sequence":"","trend":"","page":1}
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
        if(false == isset($data_obj['condition']))
        {
            return json(['code'=>500,'message'=>'isset condition false','data'=>array()]);
        }
        if(strcmp($data_obj['condition'], 'best') != 0 && strcmp($data_obj['condition'], 'recommend') != 0 && strcmp($data_obj['condition'], 'all') != 0)
        {
            return json(['code'=>500,'message'=>'condition value wrong','data'=>array()]);
        }
        if(false == isset($data_obj['category']))
        {
            return json(['code'=>500,'message'=>'isset category false','data'=>array()]);
        }
        if(false == isset($data_obj['aid']))
        {
            return json(['code'=>500,'message'=>'isset aid false','data'=>array()]);
        }
        if(false == isset($data_obj['sequence']))
        {
            return json(['code'=>500,'message'=>'isset sequence false','data'=>array()]);
        }
        if(strcmp($data_obj['sequence'], 'time') != 0 && strcmp($data_obj['sequence'], 'browse') != 0 && strcmp($data_obj['sequence'], 'price') != 0)
        {
            return json(['code'=>500,'message'=>'sequence value wrong','data'=>array()]);
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

        if(strcmp($token, $data_obj['token']) != 0)
        {
            return json(['code'=>405,'message'=>'token is wrong','data'=>array()]);
        }

        if(false == Db::table('user')->where('uuid', $data_obj['uuid'])->exists())
        {
            return json(['code'=>500,'message'=>'user uuid not exists','data'=>array()]);
        }

        $where_raw = " catalog.deny=0 ";
        if(strcmp($data_obj['condition'], 'best') == 0)
        {
            $where_raw .= " AND catalog.best=1 ";
        }
        if(strcmp($data_obj['condition'], 'recommend') == 0)
        {
            $where_raw .= " AND catalog.recommend=1 ";
        }

        if(strlen($data_obj['category']) > 0)
        {
            $where_raw .= " AND 0<>INSTR(catalog.category,'" . $data_obj['category'] . "') ";
        }
        if(strlen($data_obj['aid']) > 0)
        {
            $where_raw .= " AND catalog.aid = '" . $data_obj['aid'] . "'";
        }

        if(true == isset($data_obj['keyword']))
        {
            if(strlen($data_obj['keyword']) > 0)
            {
                $where_raw .= " AND 0<>INSTR(catalog.name, '" . $data_obj['keyword'] . "') ";
            }
        }

        $orderby_raw = "";
        if(strcmp($data_obj['sequence'], 'time') == 0)
        {
            $orderby_raw = " catalog.time ";
        }
        if(strcmp($data_obj['sequence'], 'browse') == 0)
        {
            $orderby_raw = " catalog.browse ";
        }
        if(strcmp($data_obj['sequence'], 'price') == 0)
        {
            $orderby_raw = " catalog.price ";
        }

        if(strcmp($data_obj['trend'], "asc") == 0)
        {
            $orderby_raw .= " asc ";
        }
        if(strcmp($data_obj['trend'], "desc") == 0)
        {
            $orderby_raw .= " desc ";
        }

        $catalog_array = Db::table('catalog')->select(
            'catalog.id AS catalog_id',
            'catalog.name AS catalog_name',
            'catalog.cover AS catalog_cover',
            'catalog.total AS catalog_total',
            'catalog.price AS catalog_price',
            'catalog.single AS catalog_single',
            'catalog.browse AS catalog_browse',
            'catalog.onsale AS catalog_onsale',
            'author.id AS author_id',
            'author.name AS author_name',
            'author.logo AS author_logo'
        )
        ->leftJoin('author', 'author.id', '=', 'catalog.aid')
        ->leftJoin('user', 'user.id', '=', 'author.uid')
        ->whereRaw($where_raw)->orderByRaw($orderby_raw)->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($catalog_array))
        {
            return json(['code'=>406,'message'=>'catalog_array is_null','data'=>array()]);
        }

        foreach($catalog_array as $catalog)
        {
            if($catalog->catalog_single == 1)
            {
                $catalog->catalog_browse = Db::table('production')->where('cid', '=', $catalog->catalog_id)->value('browse');
                $catalog->catalog_id = Db::table('production')->where('cid', '=', $catalog->catalog_id)->value('id');
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>$catalog_array]);
    }

    //http://116.205.243.252:12345/catalog/browse
    //{"token":"","uuid":"","cid":""}
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
        if(false == isset($data_obj['cid']))
        {
            return json(['code'=>500,'message'=>'isset cid false','data'=>array()]);
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

        if(false == Db::table('catalog')->where('id', $data_obj['cid'])->exists())
        {
            return json(['code'=>500,'message'=>'catalog id not exists','data'=>array()]);
        }

        Db::table('catalog')->where('id', $data_obj['cid'])->increment('browse');

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

}