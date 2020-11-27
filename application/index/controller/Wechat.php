<?php


namespace app\index\controller;


use think\Controller;
use think\Db;

class Wechat extends Controller
{
    public function  test(){
        return json(10);
    }
    public function auto_login(){
        $data=input('get.');
//        return json($data);
        $u_info=Db::name('user_view')
            ->where('openid',$data['openid'])
            ->find();
        if ($u_info){
            if ($u_info['jurisdiction']==7){
                $res=Db::name('stu_view')
                    ->where('openid',$data['openid'])
                    ->find();
                return  json(array('code'=>'3','info'=>$u_info,'user'=>$u_info['username'],'info2'=>$res));
            }else{
                return json(array('code'=>'1','info'=>$u_info,'user'=>$u_info['username']));
            }
        }
        $s_info=Db::name('stu_view')
            ->where('openid',$data['openid'])
            ->find();
        if ($s_info){
            return json(array('code'=>'2','info'=>$s_info,'user'=>$s_info['s_id']));
        }

    }
    public function login(){
        $data=input('post.');
//        return json($data);
        $jur=Db::name('user_view')
            ->where('username',$data['user'])
            ->where('password',md5($data['pwd']))
            ->value('jurisdiction');
        $openid=Db::name('user')
            ->where('username',$data['user'])
            ->update(['openid'=>$data['openid']]);
        if ($jur==7){//如果权限为测评班长
            $res=Db::name('user_view')
                ->where('username',$data['user'])
                ->value('user_id');
            $res2=Db::name('stu_view')
                ->where('s_proid',$res)
                ->find();
            $msg=array('code'=>'3','info'=>$jur,'info2'=>$res2,'info3'=>$res);
            return json($msg);//以班长权限退出
        }

        if ($jur==null){//判断不为管理员为普通学生/第一次查询数据失败
            $stu_exist=Db::name('stu_view')
                ->where('s_id',$data['user'])
                ->find();
            if($stu_exist==null){//第二次查询数据失败
                $msg=array('code'=>'4','info'=>'你并非本校用户');
                return json($msg);
            }
            $openid=Db::name('students')
                ->where('s_id',$data['user'])
                ->update(['openid'=>$data['openid']]);
            $msg=array('code'=>'2','info'=>$stu_exist,'info2'=>$jur);
            return json($msg);//以学生身份退出
        }
        $msg=array('code'=>'1','info'=>$jur);
        return json($msg);//以其他权限退出
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
    public function search(){
        $data=input('post.');
        if($data['jur']!=7) {
            $res = Db::name('stu_view')
                ->where('s_id', $data['s_id'])
                ->find();
            $msg=array('code'=>'1','info'=>$res,'msg'=>'查询成功');
            return json($msg);
        }else{//班长
            $user_id = Db::table('user_view')
                ->where('username',$data['user'])
                ->value('user_id');
            $class_lim=Db::table('students')
                ->where('s_proid',$user_id)
                ->value('s_class');
            $res = Db::name('stu_view')
                ->where('s_id', $data['s_id'])
                ->find();
            if ($res['s_class']!=$class_lim)
            {
                $msg=array('code'=>'3','info'=>$class_lim,'msg'=>'只能查询自己班级的学生');//这个学生信息你无权查询
                return json($msg);
            }
            $msg=array('code'=>'2','info'=>$res,'user_id'=>$user_id,'class_lim'=>$class_lim,'jur'=>$data['jur'],'msg'=>'查询成功');
            return json($msg);
        }
    }
}