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
use think\Debug;
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

    public function examination(){
        $data=request()->param();
        Db::name('score_view')
            ->where('opstate',$opstate)
            ->where($range,$limite_range)
            ->find();


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
        Debug::remark('begin');
        $res=Scorefirst::all();
        Debug::remark('end');
       echo Debug::getRangeTime('begin','end').'s';
        return json(array($res));
    }
    public function scoresec(){
        Debug::remark('begin');
        $data=request()->param();
        $res=Scorefirst::getByScoreid($data['scorefirid'])->scoresec;
        Debug::remark('end');
//      echo  Debug::getRangeTime('begin','end').'s';
        return json($res);
    }

    public function scoreoperationrun()//学分操作后台
    {
        return json(1);
        $date = input('post.');
        if ($date['jur']!=7) {
            if ($date['opscoreclass'] == "加分") {
                $date['opscoreclass'] = '1';
            } else if ($date['opscoreclass'] == "减分") {
                $date['opscoreclass'] = '2';
            }
            $time = date('Y-m-d H:i:s');
            $ip = request()->ip();
            $score = Db::name('students')
                ->where('s_id', $date['stuid'])
                ->find();
            $operinfo = [
                'ip' => $ip,
                'datetime' => $time,
                'opstate' => '1',
                'otherstate' => '0',
            ];
            $data = $date + $operinfo;
            $score1 = number_format($score['score']);//
//        $date=array('opscoreclass'=>2,'score'=>10,'stuid'=>1180131231);
//        $score=0;
            if ($date['opscoreclass'] == '1' && ($score1 >= 100 || ($date['score'] + $score1) > 100)) {
                $score_update = Db::name('students')
                    ->where('s_id', $date['stuid'])
                    ->update(['score' => '100']);
                return json(array('德育学分已满分'));
                echo "<script type='text/javascript'>parent.layer.alert('德育学分已满分');parent.history.go(-1);</script>";
            } else if ($date['opscoreclass'] == '2' && ($score1 <= 0 || ($score1 - $date['score']) < 0)) {
                //            return json('进入减分判断');
                $score_update = Db::name('students')
                    ->where('s_id', $date['stuid'])
                    ->update(['score' => '0']);
                return json(array('msg' => '德育学分已扣完', 'info2' => $date, 'info3' => $score1));
            } else {
                $scorenumcheck = Db::name("scoresec")
                    ->where('scoresecid', $date['opscoresec'])
                    ->find();
                if ($scorenumcheck['score'] >= $date['score']) {
                    return json($data);
                    $scoreopartion = Db::table('scoreoperation')->insert($data);
                    if ($data['opscoreclass'] == '1') {
                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setInc('score', $date['score']);
                    } else {
                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setDec('score', $date['score']);
                    }
                    if ($scoreopartion) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        //$this->success("对学号：{$date['stuid']} 的学生操作已被确认！");
                        return json(array($score['s_name'] . '的德育学分已被确认'));
                    } else {
                        return json(array('操作失败'));
                    }
                } else {
                    return json(array('参数异常'));
                }
            }
        }else if ($date['jur']==7){
            if ($date['opscoreclass'] == "加分") {
                $date['opscoreclass'] = '1';
            } else if ($date['opscoreclass'] == "减分") {
                $date['opscoreclass'] = '2';
            }
            $time = date('Y-m-d H:i:s');
            $ip = request()->ip();
            $operinfo = [
                'ip' => $ip,
                'datetime' => $time,
                'opstate' => '2',
                'otherstate' => '0',
            ];
            $data = $date + $operinfo;
//            $validate = new validate([
//                ['stuid', 'require|regex:int|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
//                ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
//                ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
//                ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类参数错误，请返回重试！'],
//                ['opscoreclass', 'require|regex:int', '请选择操作类型！|操作类型参数错误，请返回重试！'],
//                ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
//            ]);
                $scorenumcheck = Db::name("scoresec")
                    ->where('scoresecid', $date['opscoresec'])
                    ->find();
                if ($scorenumcheck['score'] >= $date['score']) {

                    $scoreopartion = Db::table('scoreoperation')->insert($data);
                    if ($scoreopartion) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        return json(array('德育学分操作已被确认'));
                    } else {
                        return json(array('操作失败'));
                    }
                } else {
                    return json(array('操作不能超过分数上线'));
                }

        }else{
            return json(array('参数异常'));
        }

    }
    public function examinerun()//审核操作
    {
        $data = input('post.');
        $stateupdate = [
            'opstate' => '1',
        ];//#########################################根据权限需要修改一下代码块的相关代表状态的参数
        $date = $data + $stateupdate;
//        $validate = new validate([
//            ['opstate', 'require|regex:int', '请选择操作类型！|操作当前状态参数异常，请返回重试！'],
//            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
//            ['id', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
//            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
//            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],
//        ]);
            $checkclass = Db::table('scoreoperation')
                ->where('opstate', '1')
                ->where('id', $date['id'])
                ->select();//用户名重复性检测
            if ($checkclass) {
                echo "<script type='text/javascript'>parent.layer.alert('该操作已审核通过，请勿重复提交相同操作！');parent.history.go(-1)</script>";
            } else {
                $checkusr = Db::table('user')
                    ->where('username', $date['username'])
                    ->where('u_name', $date['othername'])
                    ->select();//用户名重复性检测
                if ($checkusr) {
                    $time = date('Y-m-d H:i:s');
                    $editscore = Db::table('scoreoperation')
                        ->where('id', $date['id'])
                        ->update([
                            'opstate' => '1',
                            'othername' => $date['othername'],
                            'othertime' => $time,
                            'otherstate' => '1',
                            'info' => $date['info']]);//修改操作
                    if ($editscore) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '对学生操作流水号为为：' . $date['id'] . ' 进行了操作。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        echo "<script type='text/javascript'>parent.layer.alert('操作成功！');parent.history.go(-1);</script>";
                        exit;
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                        exit;//判断更新操作是否成功
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('参数错误！');parent.history.go(-1);</script>";
                    exit;
                }
        }
    }

}