<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;

class user
{
    //http://116.205.243.252:12345/user/register
    //{"code":"","user":"","pwd":"","nick":"","invite":""}
    public function register(Request $request)
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
        if(false == isset($data_obj['user']))
        {
            return json(['code'=>500,'message'=>'isset user false','data'=>array()]);
        }
        if(false == isset($data_obj['pwd']))
        {
            return json(['code'=>500,'message'=>'isset pwd false','data'=>array()]);
        }
        if(false == isset($data_obj['code']))
        {
            return json(['code'=>500,'message'=>'isset code false','data'=>array()]);
        }
        if(false == isset($data_obj['nick']))
        {
            return json(['code'=>500,'message'=>'isset nick false','data'=>array()]);
        }

        $timestamp = Redis::get("clicaptcha-" . $data_obj['user']);
        if(is_null($timestamp))
        {
            $timestamp = 0;
        }
        if(time() - $timestamp > 300)
        {
            return json(['code'=>400,'message'=>'code is expired','data'=>array()]);
        }
        $code = Redis::get("smscode-" . $data_obj['user']);
        if(is_null($code))
        {
            $code = "";
        }
        if((strcmp($code, $data_obj['code']) != 0) || strlen($code) == 0)
        {
            return json(['code'=>401,'message'=>'code is wrong','data'=>array()]);
        }

        Db::table('user')->insert([
            'name' => $data_obj['user'],
            'password' => hash('sha512',$data_obj['pwd']),
            'nick' => $data_obj['nick']
        ]);

        if(true == isset($data_obj['invite']))
        {
            if(strlen($data_obj['invite']) > 0)
            {
                $mine_obj = Db::table('user')->select('id')->where([
                    ['name', '=', $data_obj['user']],
                    ['password', '=', hash('sha512',$data_obj['pwd'])],
                    ['nick', '=', $data_obj['nick']]
                ])->limit(1)->first();
                if(is_null($mine_obj))
                {
                    return json(['code'=>500,'message'=>'mine_obj is_null','data'=>array()]);
                }

                $user_obj = Db::table('user')->select('id')->where([
                    ['deny', '=', 0],
                    ['uuid', '=', $data_obj['invite']]
                ])->limit(1)->first();
                if(is_null($user_obj))
                {
                    return json(['code'=>500,'message'=>'user_obj is_null','data'=>array()]);
                }

                Db::table('invite')->insert([
                    'uid' => $user_obj->id,
                    'accept' => $mine_obj->id
                ]);
            }
        }

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/user/login
    //{"user":"","pwd":""}
    public function login(Request $request)
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
            return json(['code'=>500,'message'=>'json_decode is_null','data'=>array()]);
        }
        if(false == isset($data_obj['user']))
        {
            return json(['code'=>500,'message'=>'isset user false','data'=>array()]);
        }
        if(false == isset($data_obj['pwd']))
        {
            return json(['code'=>500,'message'=>'isset pwd false','data'=>array()]);
        }

        $timestamp = Redis::get("login-" . $data_obj['user']);
        if(is_null($timestamp))
        {
            $timestamp = 0;
        }
        if(time() - $timestamp < 2) {
            //登录限流，间隔两秒
            return json(['code'=>402,'message'=>'user_obj is_null','data'=>array()]);
        }

        Redis::set('login-' . $data_obj['user'], time());

        $user_obj = Db::table('user')->select('id', 'name', 'uuid', 'bcid', 'nick', 'description', 'avatar', 'certified', 'privacy')->where([
            ['deny', '=', 0],
            ['name', '=', $data_obj['user']],
            ['password', '=', hash('sha512',$data_obj['pwd'])]
        ])->limit(1)->first();
        if(is_null($user_obj))
        {
            Db::table('login')->insert([
                'name' => $data_obj['user'],
                'result' => 1
            ]);

            return json(['code'=>403,'message'=>'user_obj is_null','data'=>array()]);
        }

        Db::table('login')->insert(
            ['name' => $data_obj['user'], 'uid' => $user_obj->id, 'result' => 0]
        );

        $token = hash('sha512', $data_obj['user'] . $user_obj->uuid . $user_obj->description . microtime());
        Redis::set('token-' . $user_obj->uuid, $token);
        $user_obj->token = $token;

        return json(['code'=>200,'message'=>'ok','data'=>$user_obj]);
    }

    //http://116.205.243.252:12345/user/logout
    //{"token":"","uuid":""}
    public function logout(Request $request)
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

        if(strcmp($token, $data_obj['token']) != 0 || strlen($token) == 0)
        {
            return json(['code'=>405,'message'=>'token is wrong','data'=>array()]);
        }

        Redis::del('token-' . $data_obj['uuid']);

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/user/password
    //{"code":"","user":"","pwd":""}
    public function password(Request $request)
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
        if(false == isset($data_obj['user']))
        {
            return json(['code'=>500,'message'=>'isset user false','data'=>array()]);
        }
        if(false == isset($data_obj['pwd']))
        {
            return json(['code'=>500,'message'=>'isset pwd false','data'=>array()]);
        }
        if(false == isset($data_obj['code']))
        {
            return json(['code'=>500,'message'=>'isset code false','data'=>array()]);
        }

        $timestamp = Redis::get("clicaptcha-" . $data_obj['user']);
        if(is_null($timestamp))
        {
            $timestamp = 0;
        }
        if(time() - $timestamp > 300)
        {
            return json(['code'=>400,'message'=>'code is expired','data'=>array()]);
        }
        $code = Redis::get("smscode-" . $data_obj['user']);
        if(is_null($code))
        {
            $code = '';
        }
        if(strcmp($code, $data_obj['code']) != 0 || strlen($code) == 0)
        {
            return json(['code'=>401,'message'=>'code is wrong','data'=>array()]);
        }

        $update_obj = array();
        $update_obj['password'] = hash('sha512',$data_obj['pwd']);

        Db::table('user')->where('name', '=', $data_obj['user'])->update($update_obj);

        return json(['code'=>200,'message'=>'ok','data'=>array()]);
    }

    //http://116.205.243.252:12345/user/modify
    //{"token":"","uuid":"","nick":""}
    public function modify(Request $request)
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

        $update_obj = array();
        if(false != isset($data_obj['nick']))
        {
            $update_obj['nick'] = $data_obj['nick'];
        }
        if(false != isset($data_obj['description']))
        {
            $update_obj['description'] = $data_obj['description'];
        }
        if(false != isset($data_obj['avatar']))
        {
            $update_obj['avatar'] = $data_obj['avatar'];
        }
        if(false != isset($data_obj['realname']))
        {
            $update_obj['realname'] = $data_obj['realname'];
        }
        if(false != isset($data_obj['idcardno']))
        {
            $update_obj['idcardno'] = $data_obj['idcardno'];
        }
        if(false != isset($data_obj['privacy']))
        {
            $update_obj['privacy'] = $data_obj['privacy'];
        }
        Db::table('user')->where('uuid', '=', $data_obj['uuid'])->update($update_obj);

        $user_obj = Db::table('user')->select('id', 'name', 'uuid', 'bcid', 'nick', 'description', 'avatar', 'certified', 'privacy')->where([
            ['uuid', '=', $data_obj['uuid']]
        ])->limit(1)->first();
        if(is_null($user_obj))
        {
            return json(['code'=>403,'message'=>'user_obj is_null','data'=>array()]);
        }
        return json(['code'=>200,'message'=>'ok','data'=>$user_obj]);
    }

    //http://116.205.243.252:12345/user/fetch
    //{"token":"","uuid":"","anonymous":""}
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
        if(false == isset($data_obj['anonymous']))
        {
            return json(['code'=>500,'message'=>'isset anonymous false','data'=>array()]);
        }
        if(strcmp($data_obj['uuid'], $data_obj['anonymous']) == 0)
        {
            return json(['code'=>500,'message'=>'uuid equal anonymous','data'=>array()]);
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

        $user_obj = Db::table('user')->select('name', 'uuid', 'bcid', 'nick', 'description', 'avatar')->where([
            ['deny', '=', 0],
            ['uuid', '=', $data_obj['anonymous']]
        ])->limit(1)->first();

        if(is_null($user_obj))
        {
            return json(['code'=>406,'message'=>'user_obj is_null','data'=>array()]);
        }

        return json(['code'=>200,'message'=>'ok','data'=>$user_obj]);
    }

    //http://116.205.243.252:12345/user/share
    //{"token":"","uuid":""}
    public function share(Request $request)
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

        $invite_url = "http://116.205.243.252/h5/#/pages/login/login?tabCurrentIndex=1&uuid=" . $data_obj['uuid'];
        $qrcode_image_file = save_qrcode_to_image($invite_url);
        $qrcode_image = imagecreatefrompng($qrcode_image_file);
        $qrcode_w = imagesx($qrcode_image);
        $qrcode_h = imagesy($qrcode_image);
        if(is_null($qrcode_image))
        {
            return json(['code'=>500,'message'=>'save_qrcode_to_image fail','data'=>array()]);
        }

        $posters_array = Db::table('posters')->select('image', 'top', 'left', 'width', 'height')->get();
        if(is_null($posters_array))
        {
            return json(['code'=>406,'message'=>'posters_array is_null','data'=>array()]);
        }

        $image_array = array();
        $image_array[] = 'http://116.205.243.252:12345/' . basename($qrcode_image_file);
        foreach($posters_array as $posters)
        {
            $posters_image_file = public_path() . DIRECTORY_SEPARATOR . md5($invite_url) . '-' . $posters->image;
            $image_array[] = 'http://116.205.243.252:12345/' . basename($posters_image_file);
            if(file_exists($posters_image_file))
            {
                continue;
            }

            $image_bg = imagecreatefromstring(file_get_contents(public_path() . DIRECTORY_SEPARATOR . $posters->image));

            $new_qrcode_image = imagecreatetruecolor($posters->width, $posters->height);
            imagecopyresampled($new_qrcode_image, $qrcode_image, 0, 0, 0, 0, $posters->width, $posters->height, $qrcode_w, $qrcode_h);
            imagecopymerge($image_bg, $new_qrcode_image, $posters->left, $posters->top, 0, 0, $posters->width, $posters->height, 100);

            imagejpeg($image_bg, $posters_image_file);

            imagedestroy($new_qrcode_image);
            imagedestroy($image_bg);
        }
        imagedestroy($qrcode_image);

        return json(['code'=>200,'message'=>'ok','data'=>$image_array]);
    }
}
