<?php


namespace app\index\controller;


use think\Controller;
use think\Db;

class Wechat extends Controller
{
    public function  test(){
        return json(10);
    }
    public function login(){
        $data=input('post.');
//        return json($data);
        $jur=Db::name('user')
            ->where('username',$data['user'])
            ->where('password',md5($data['pwd']))
            ->value('jurisdiction');
        return json($jur);
    }
    public function Wx_GetOpenidByCode(){
        $code = $_REQUEST['code'];//获取code
        $appid ="wx4be213e6cd487a76";
        $secret = "e01fdbfb23b73a4f72f9525d52251e0c";
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        //通过code换取网页授权access_token
        $weixin =  file_get_contents($url);
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        $openid = $array['openid'];//输出openid'openid'=>$openid,
        $sessionKey=$array['session_key'];
        return json(array('openid'=>$openid,'array'=>$array));
    }
    public function getUserPhone() {
        vendor('getphone.wxBizDataCrypt');
        $code = input();
        //  return json($code);
        $appid = 'wxa89f13d1cea45d90';  //企业appid wxa89f13d1cea45d90 wx4be213e6cd487a76
        $secret = '2562a38d1fa2b43ebff915a8380f84e1';  //企业secret 2562a38d1fa2b43ebff915a8380f84e1 e01fdbfb23b73a4f72f9525d52251e0c
        $encryptedData = $code['encryptedData'];   //包括敏感数据在内的完整用户信息的加密数据
        $jscode=$code['code'];   //用户登录授权获取到的code
        //用code  获取sessionkey
        $access_token='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$jscode.'&grant_type=authorization_code';
        $result = $this->curlOpen($access_token);
        $jsonarr = json_decode($result, true);
        $sessionKey = $code['session_key'];
        $iv = $code['iv'];
        $pc = new \WXBizDataCrypt($appid, $sessionKey); //注意使用\进行转义
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        return json(array('k1'=>$pc,'k2'=>$errCode,'k3'=>$data));

    }
}