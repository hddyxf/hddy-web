<?php


namespace app\index\controller;


use app\index\model\Scoresec;
use app\index\model\Students;
use app\index\model\Users;
use think\Controller;
use think\Db;
use app\index\controller\Formcheck;
use app\index\model\User;
use app\index\model\Student;
use app\index\model\Scorefirst;
use think\Validate;

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
        $check_userinfo=Db::name('user_view')
            ->where('username',$data['user'])
            ->where('password',md5($data['pwd']))
            ->find();
        if (empty($check_userinfo)||$check_userinfo['jurisdiction']==7){//此用户并非权限用户以及班长用户（数据获取阶段）（校外或者学生用户）
            $check_stuinfo=Db::name('stu_view')
                ->where('s_id',$data['user'])
                ->where('s_proid',$data['pwd'])
                ->find();
            if (empty($check_stuinfo)){//并非正常权限账户和学生账户→可能为班长和校外账户
                $check_mnt=Db::name('user_stu_view')
                    ->where('username',$data['user'])
                    ->where('password',$data['pwd'])
                    ->find();
                    if(empty($check_mnt)){
                        return json(array('code'=>'4','msg'=>'校外账户'));
                    }elseif(!empty($check_mnt)){
                        return json(array('code'=>'2','info'=>$check_mnt,'msg'=>'此账户为班长账户'));
                    }else{
                        return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo,'err_info_2'=>$check_stuinfo,'err_info_3'=>$check_mnt));
                    }
            }elseif(!empty($check_stuinfo)){//学生账户
                return json(array('code'=>'1','info'=>$check_stuinfo,'msg'=>'此账户为学生账户'));
            }else{
                return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo,'err_info_2'=>$check_stuinfo));
            }
            }elseif(!empty($check_userinfo)){//此为除了班长和学生和校外之外的账户,已经判断权限账户存在
            if ($check_userinfo['jurisdiction']==10){
                return json(array('code'=>'5','info'=>$check_userinfo,'msg'=>'此账户为楼长账户'));
            }else{
                return json(array('code'=>'3','info'=>$check_userinfo,'msg'=>'此账户为其他权限账户'));
            }
        }else{
            return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo));

        }
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
        $data=input('get.');
        $data1=array(
            '2'=>array('jur'=>$data['jur'],'limit_table'=>'u_colle_view','limit_data'=>'collegeinfo','sid'=>$data['s_id']),
            '4'=>array('username'=>$data['username'],'limit_table'=>'u_colle_view','limit_data'=>'collegeinfo','sid'=>$data['s_id']),
            '5'=>array('username'=>$data['username'],'limit_table'=>'u_colle_view','limit_data'=>'collegeinfo','sid'=>$data['s_id']),
            '6'=>array('username'=>$data['username'],'limit_table'=>'user_view','limit_data'=>'u_name','sid'=>$data['s_id']),
            '7'=>array('username'=>$data['username'],'limit_table'=>'user_stu_view','limit_data'=>'s_class','sid'=>$data['s_id']),
            '9'=>array('jur'=>$data['jur'],'limit_table'=>'u_colle_view','limit_data'=>'collegeinfo','sid'=>$data['s_id']),
            '10'=>array('username'=>$data['username'],'limit_table'=>'u_apart_view','limit_data'=>'apartmentinfo','sid'=>$data['s_id']),
        );
        $res=call_user_func_array(array('app\index\controller\Formcheck','limit_select_user'),array($data1[$data['jur']]));
        if ($res){
            $res="查询异常";
        }
        return json($res);
    }


    public function test1(){
        $students=Student::get(1);
        var_dump($students->users());
        exit();
//        return json($res);
    }
    public function select(){
        $data=request()->param();
            $res=Db::name($data['table'])
                ->where($data['Field'],$data['param'])
                ->find();
            return json($res);
    }
    public function scorefirst(){
        $res=Scorefirst::all();
        return json(array($res));
    }
    public function scoresec(){
        $data=request()->param();
        $res=Scorefirst::getByScoreid($data['scorefirid'])->scoresec;
        return json($res);
    }

}