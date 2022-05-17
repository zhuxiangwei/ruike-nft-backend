<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class author
{
    //http://116.205.243.252:12345/author/request
    //{"token":"","uuid":"","type":1,"name":"","contact":"","phone":"","wechat":"","skill":"","note":"","images":""}
    public function request(Request $request)
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
        if(false == isset($data_obj['name']))
        {
            return json(['code'=>500,'message'=>'isset name false','data'=>array()]);
        }
        if(false == isset($data_obj['contact']))
        {
            return json(['code'=>500,'message'=>'isset contact false','data'=>array()]);
        }
        if(false == isset($data_obj['phone']))
        {
            return json(['code'=>500,'message'=>'isset phone false','data'=>array()]);
        }
        if(false == isset($data_obj['wechat']))
        {
            return json(['code'=>500,'message'=>'isset wechat false','data'=>array()]);
        }
        if(false == isset($data_obj['skill']))
        {
            return json(['code'=>500,'message'=>'isset skill false','data'=>array()]);
        }
        if(false == isset($data_obj['note']))
        {
            return json(['code'=>500,'message'=>'isset note false','data'=>array()]);
        }
        if(false == isset($data_obj['images']))
        {
            return json(['code'=>500,'message'=>'isset images false','data'=>array()]);
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
        Db::table('author')->insert([
            'uid' => $mine_obj->id,
            'type' => $data_obj['type'],
            'name' => $data_obj['name'],
            'contact' => $data_obj['contact'],
            'phone' => $data_obj['phone'],
            'wechat' => $data_obj['wechat'],
            'skill' => $data_obj['skill'],
            'note' => $data_obj['note']
        ]);
        Db::table('request')->insert(
            ['uid' => $mine_obj->id, 'type' => 1, 'images' => $data_obj['images']]
        );
        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    public function time_comparator_asc($object1, $object2) {
        return strtotime($object1['time']) - strtotime($object2['time']);
    }

    public function production_comparator_asc($object1, $object2) {
        return $object1['production_count'] - $object2['production_count'];
    }

    public function sales_comparator_asc($object1, $object2) {
        return $object1['sales_count'] - $object2['sales_count'];
    }

    public function all_comparator_asc($object1, $object2) {
        return (strtotime($object1['time']) + $object1['production_count'] + $object1['sales_count']) - (strtotime($object2['time']) + $object2['production_count'] + $object2['sales_count']);
    }

    public function time_comparator_desc($object2, $object1) {
        return strtotime($object1['time']) - strtotime($object2['time']);
    }

    public function production_comparator_desc($object2, $object1) {
        return $object1['production_count'] - $object2['production_count'];
    }

    public function sales_comparator_desc($object2, $object1) {
        return $object1['sales_count'] - $object2['sales_count'];
    }

    public function all_comparator_desc($object2, $object1) {
        return (strtotime($object1['time']) + $object1['production_count'] + $object1['sales_count']) - (strtotime($object2['time']) + $object2['production_count'] + $object2['sales_count']);
    }

    //http://116.205.243.252:12345/author/skill
    //{"token":"","uuid":""}
    public function skill(Request $request)
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

        $skill_array = Db::table('author')->select('skill')->get();
        $skill_string = "";
        foreach($skill_array as $skill)
        {
            if(strlen($skill->skill) > 0)
            {
                if(strlen($skill_string) > 0)
                {
                    $skill_string = $skill_string . "," . $skill->skill;
                }
                else
                {
                    $skill_string = $skill->skill;
                }
            }
        }
        $skill_array = explode(',', $skill_string);
        $skill_array = array_unique($skill_array);
        $skill_array = array_values($skill_array);

        return json(['code'=>200,'message'=>'ok','data'=>$skill_array]);
    }

    //http://116.205.243.252:12345/author/list
    //{"token":"","uuid":"","keyword":"","skill":"","sequence":"","trend":"","page":1}
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
        if(false == isset($data_obj['skill']))
        {
            return json(['code'=>500,'message'=>'isset skill false','data'=>array()]);
        }
        if(false == isset($data_obj['sequence']))
        {
            return json(['code'=>500,'message'=>'isset sequence false','data'=>array()]);
        }
        if(strcmp($data_obj['sequence'], '入驻时间') != 0 && strcmp($data_obj['sequence'], '作品数量') != 0 && strcmp($data_obj['sequence'], '售卖数量') != 0 && strcmp($data_obj['sequence'], '综合') != 0)
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

        $mine_obj = Db::table('user')->select('id')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($mine_obj))
        {
            return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
        }

        $where_raw = " 0<>INSTR(author.skill,'" . $data_obj['skill'] . "') ";

        if(strcmp($data_obj['skill'], '全部') == 0)
        {
            $where_raw = " 1 ";
        }

        if(true == isset($data_obj['keyword']))
        {
            if(strlen($data_obj['keyword']) > 0)
            {
                $where_raw .= " AND 0<>INSTR(author.name, '" . $data_obj['keyword'] . "') ";
            }
        }

        $author_array = Db::table('author')->select(
            'author.id',
            'author.uid',
            'author.name',
            'author.logo',
            'author.time'
        )
        ->whereRaw($where_raw)->offset(($data_obj['page']-1)*10)->limit(10)->get();

        if(is_null($author_array))
        {
            return json(['code'=>406,'message'=>'author_array is_null','data'=>array()]);
        }

        foreach($author_array as $author)
        {
            $author->production_count = Db::table('production')->select('production.id')->where('production.aid', '=', $author->id)->count();

            $author->favorite_count = Db::table('favorite')->select('favorite.id')->where('favorite.aid', '=', $author->id)->count();

            $author->favorite = Db::table('favorite')->where([['aid', '=', $author->id], ['uid', '=', $mine_obj->id]])->exists() ? 1 : 0;

            $author->sales_count = Db::table('order')->select('order.id')
            ->leftJoin('production', 'production.id', '=', 'order.pid')
            ->leftJoin('author', 'author.id', '=', 'production.aid')
            ->where([['author.id', '=', $author->id], ['order.status', '=', '1']])
            ->groupBy('order.pid')->count();
        }

        $author_array = json_decode(json_encode($author_array), true);

        if(strcmp($data_obj['sequence'], '入驻时间') == 0)
        {
            if(strcmp($data_obj['trend'], "asc") == 0)
            {
                usort($author_array, array($this, 'time_comparator_asc'));
            }
            if(strcmp($data_obj['trend'], "desc") == 0)
            {
                usort($author_array, array($this, 'time_comparator_desc'));
            }
        }

        if(strcmp($data_obj['sequence'], '作品数量') == 0)
        {
            if(strcmp($data_obj['trend'], "asc") == 0)
            {
                usort($author_array, array($this, 'production_comparator_asc'));
            }
            if(strcmp($data_obj['trend'], "desc") == 0)
            {
                usort($author_array, array($this, 'production_comparator_desc'));
            }
        }

        if(strcmp($data_obj['sequence'], '售卖数量') == 0)
        {
            if(strcmp($data_obj['trend'], "asc") == 0)
            {
                usort($author_array, array($this, 'sales_comparator_asc'));
            }
            if(strcmp($data_obj['trend'], "desc") == 0)
            {
                usort($author_array, array($this, 'sales_comparator_desc'));
            }
        }

        if(strcmp($data_obj['sequence'], '综合') == 0)
        {
            if(strcmp($data_obj['trend'], "asc") == 0)
            {
                usort($author_array, array($this, 'all_comparator_asc'));
            }
            if(strcmp($data_obj['trend'], "desc") == 0)
            {
                usort($author_array, array($this, 'all_comparator_desc'));
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>$author_array]);
    }

    //http://116.205.243.252:12345/author/fetch
    //{"token":"","uuid":"","aid":""}
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
        if(false == isset($data_obj['aid']))
        {
            return json(['code'=>500,'message'=>'isset aid false','data'=>array()]);
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

        $author_obj = Db::table('user')->select(
            'user.uuid AS user_uuid',
            'user.bcid AS user_bcid',
            'author.id AS author_id',
            'author.type AS author_type',
            'author.name AS author_name',
            'author.contact AS author_contact',
            'author.phone AS author_phone',
            'author.wechat AS author_wechat',
            'author.skill AS author_skill',
            'author.logo AS author_logo'
        )
        ->leftJoin('author', 'author.uid', '=', 'user.id')
        ->where([
            ['user.deny', '=', 0],
            ['author.uid', '=', $data_obj['aid']]
        ])->limit(1)->first();

        if(is_null($author_obj))
        {
            return json(['code'=>406,'message'=>'author_obj is_null','data'=>array()]);
        }

        $author_obj->favorite = Db::table('favorite')->where([['aid', '=', $data_obj['aid']], ['uid', '=', $mine_obj->id]])->exists() ? 1 : 0;

        return json(['code'=>200,'message'=>'ok','data'=>$author_obj]);
    }
}