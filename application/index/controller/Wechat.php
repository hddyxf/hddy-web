<?php


namespace app\index\controller;


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
//    public function auto_login(){
//        $data=input('get.');
////        return json($data);
//        $u_info=Db::name('user_view')
//            ->where('openid',$data['openid'])
//            ->find();
//        if ($u_info){
//            if ($u_info['jurisdiction']==7){
//                $res=Db::name('stu_view')
//                    ->where('openid',$data['openid'])
//                    ->find();
//                return  json(array('code'=>'3','info'=>$u_info,'user'=>$u_info['username'],'info2'=>$res));
//            }else{
//                return json(array('code'=>'1','info'=>$u_info,'user'=>$u_info['username']));
//            }
//        }
//        $s_info=Db::name('stu_view')
//            ->where('openid',$data['openid'])
//            ->find();
//        if ($s_info){
//            return json(array('code'=>'2','info'=>$s_info,'user'=>$s_info['s_id']));
//        }
//
//    }
//    public function login(){
//        $data=input('post.');
//        $check_userinfo=Db::name('user_view')
//            ->where('username',$data['user'])
//            ->where('password',md5($data['pwd']))
//            ->find();
//        if (empty($check_userinfo)||$check_userinfo['jurisdiction']==7){//此用户并非权限用户以及班长用户（数据获取阶段）（校外或者学生用户）
//            $check_stuinfo=Db::name('stu_view')
//                ->where('s_id',$data['user'])
//                ->where('s_proid',$data['pwd'])
//                ->find();
//            if (empty($check_stuinfo)){//并非正常权限账户和学生账户→可能为班长和校外账户
//                $check_mnt=Db::name('user_stu_view')
//                    ->where('username',$data['user'])
//                    ->where('password',$data['pwd'])
//                    ->find();
//                    if(empty($check_mnt)){
//                        return json(array('code'=>'4','msg'=>'校外账户'));
//                    }elseif(!empty($check_mnt)){
//                        return json(array('code'=>'2','info'=>$check_mnt,'msg'=>'此账户为班长账户'));
//                    }else{
//                        return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo,'err_info_2'=>$check_stuinfo,'err_info_3'=>$check_mnt));
//                    }
//            }elseif(!empty($check_stuinfo)){//学生账户
//                return json(array('code'=>'1','info'=>$check_stuinfo,'msg'=>'此账户为学生账户'));
//            }else{
//                return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo,'err_info_2'=>$check_stuinfo));
//            }
//            }elseif(!empty($check_userinfo)){//此为除了班长和学生和校外之外的账户,已经判断权限账户存在
//            if ($check_userinfo['jurisdiction']==10){
//                return json(array('code'=>'5','info'=>$check_userinfo,'msg'=>'此账户为楼长账户'));
//            }else{
//                return json(array('code'=>'3','info'=>$check_userinfo,'msg'=>'此账户为其他权限账户'));
//            }
//        }else{
//            return json(array('code'=>'4','msg'=>'查询异常','err_info_1'=>$check_userinfo));
//
//        }
//    }
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

//    public function search()
//    {
//        $data = input('get.');
//        $lsu = new Formcheck();
//        $username = $data['username'];
//        $s_id = $data['s_id'];
//        if ($data['jur'] == 4 || $data['jur'] == 5) {
//            $res = $lsu->limit_select_user($data['username'], 'u_colle_view', 'collegeinfo', 'collegeinfo', $data['s_id']);
//        } elseif ($data['jur'] == 6) {
//            $res = $lsu->limit_select_user($username, 'user_view', 'teacherinfo', 'u_name', $s_id);
//        } elseif ($data['jur'] == 10) {
//            $res = $lsu->limit_select_user($data['username'], 'u_apart_view', 'apartmentinfo', 'apartmentinfo', $data['s_id']);
////            return json(1);
//        } elseif ($data['jur'] == 7) {
//            $res = $lsu->limit_select_user($username, 'user_stu_view', 's_class', 's_class', $s_id);
//        } elseif ($data['jur'] == 9) {
//            $res = Db::name('stu_view')
//                ->where('s_id', $data['s_id'])
//                ->find();
//        } elseif ($data['jur'] == 2) {
//            $res = Db::name('stu_view')
//                ->where('s_id', $data['s_id'])
//                ->find();
//        } else {
//            $res = "查询异常";
//        }
//        return json($res);
//    }



    public function test1(){
        $students=Student::get(1);
        var_dump($students->users());
        exit();
//        return json($res);
    }
//    public function select(){
//        $data=request()->param();
//            $res=Db::name($data['table'])
//                ->where($data['Field'],$data['param'])
//                ->find();
//            return json($res);
//    }
//    public function scorefirst()
//    {
//        $res = Db::name('scorefirst')
//            ->select();
//        return json($res);
//    }
//    public function scoresec()//二级联动---二级分类
//    {
//        $scoresec = input('get.');
//        $score = Db::name("scoresec_view")
//            ->where('scorefirid', $scoresec['q'])
//            ->select();
//        $count = Db::name("scoresec")
//            ->where('scorefirid', $scoresec['q'])
//            ->count("scorefirid");
//        return json($score);
//    }

    public function examinerun()//审核操作
    {
        $data = input('post.');
//        halt($data);
        $stateupdate = [
            'opstate' => '1',
            'info'=>'小程序'
        ];//#########################################根据权限需要修改一下代码块的相关代表状态的参数
        $date = $data + $stateupdate;
        $validate = new validate([
            ['opstate', 'require|regex:int', '请选择操作类型！|操作当前状态参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '审核学分操作流水号为：' . $date['id'] . '的信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
//            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
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
                    $scoreinfo = Db::table('scoreoperation')
                        ->where('id', $date['id'])
                        ->find();//修改操作$scoreinfo['score']
                    if ($scoreinfo['opscoreclass'] == '1') {
                        $opres = Db::table('students')
                            ->where('s_id', $scoreinfo['stuid'])
                            ->setInc('score', 1);
                    } else if ($scoreinfo['opscoreclass'] == '2'){
                        $opres = Db::table('students')
                            ->where('s_id', $scoreinfo['stuid'])
                            ->setDec('score', 1);
                    }
//                    halt($opres);
                    $editscore = Db::table('scoreoperation')
                        ->where('id', $date['id'])
                        ->update([
                            'opstate' => '6',
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
//    public function syslog()//系统操作日志页面
//    {
//        $data=request()->param();
//        $res=Db::table('score_view')
//            ->where('username',$data['username'])
//            ->select();
//        return json($res);
//    }
    public function examinelist()//待审核操作列表后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('opstate', '2')//根据权限修改where条件
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('opstate', '2')//根据权限修改where条件
            ->order('datetime desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($cate_list);
    }
//    public function examineonly()//待审核操作列表后台
//    {
//        $data=request()->param();
//        $cate_list = Db::name("score_view")
//            ->where('opstate', '2')//根据权限修改where条件
//            ->where('id',$data['id'])
//            ->order('datetime desc')
//            ->find();
//        return json($cate_list);
//    }
//    public function test()
//    {
//        return json(10);
//    }

    public function auto_login()
    {
        $data = input('get.');
//        return json($data);
        $u_info = Db::name('user_view')
            ->where('openid', $data['openid'])
            ->find();
        if ($u_info) {
            if ($u_info['jurisdiction'] == 7) {
                $res = Db::name('stu_view')
                    ->where('openid', $data['openid'])
                    ->find();
                return json(array('code' => '3', 'info' => $u_info, 'user' => $u_info['username'], 'info2' => $res));
            } else {
                return json(array('code' => '1', 'info' => $u_info, 'user' => $u_info['username']));
            }
        }
        $s_info = Db::name('stu_view')
            ->where('openid', $data['openid'])
            ->find();
        if ($s_info) {
            return json(array('code' => '2', 'info' => $s_info, 'user' => $s_info['s_id']));
        }

    }


    public function login()
    {
        $data = input('post.');
        $check_userinfo = Db::name('user_view')
            ->where('username', $data['user'])
            ->where('password', md5($data['pwd'])) //md5解密
            ->find();
        //查询有无此用户
        if (empty($check_userinfo) || $check_userinfo['jurisdiction'] == 7) {//此用户并非权限用户以及班长用户（数据获取阶段）（校外或者学生用户）
            $check_stuinfo = Db::name('stu_view') //学生
            ->where('s_id', $data['user'])
                ->where('s_proid', $data['pwd'])
                ->find();
            if (empty($check_stuinfo)) {//并非正常权限账户和学生账户→可能为班长和校外账户
                $check_mnt = Db::name('u_stu_view') //测评班长
                ->where('username', $data['user'])
                    ->where('password', md5($data['pwd']))
                    ->find();
                if (empty($check_mnt)) {//也非班长
                    return json(array('code' => '4', 'msg' => '校外账户'));
                } elseif (!empty($check_mnt)) {
                    return json(array('code' => '2', 'info' => $check_mnt, 'msg' => '此账户为班长账户'));
                } else {
                    return json(array('code' => '4', 'msg' => '查询异常', 'err_info_1' => $check_userinfo, 'err_info_2' => $check_stuinfo, 'err_info_3' => $check_mnt));
                }
            }    elseif (!empty($check_stuinfo)) {//学生账户
                return json(array('code' => '1', 'info' => $check_stuinfo, 'msg' => '此账户为学生账户'));
            } else {
                return json(array('code' => '4', 'msg' => '查询异常', 'err_info_1' => $check_userinfo, 'err_info_2' => $check_stuinfo));
            }
        } elseif (!empty($check_userinfo)) {//此为除了班长和学生和校外之外的账户,已经判断权限账户存在
            if ($check_userinfo['jurisdiction'] == 10) {
                return json(array('code' => '5', 'info' => $check_userinfo, 'msg' => '此账户为楼长账户'));
            } else {
                return json(array('code' => '3', 'info' => $check_userinfo, 'msg' => '此账户为除班长、学生、楼长外的权限账户'));
            }
        } else {
            return json(array('code' => '4', 'msg' => '查询异常', 'err_info_1' => $check_userinfo));

        }
    }

//    public function Wx_GetOpenidByCode()
//    {
//        $code = $_REQUEST['code'];//获取code
//        $appid = "wx4be213e6cd487a76";
//        $secret = "e01fdbfb23b73a4f72f9525d52251e0c";
//        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
//        //通过code换取网页授权access_token
//        $weixin = file_get_contents($url);
//        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
//        $array = get_object_vars($jsondecode);//转换成数组
//        $openid = $array['openid'];//输出openid'openid'=>$openid,
//        $sessionKey = $array['session_key'];
//        return json(array('openid' => $openid, 'array' => $array));
//    }

//    public function getUserPhone()
//    {
//        vendor('getphone.wxBizDataCrypt');
//        $code = input();
//        //  return json($code);
//        $appid = 'wxa89f13d1cea45d90';  //企业appid wxa89f13d1cea45d90 wx4be213e6cd487a76
//        $secret = '2562a38d1fa2b43ebff915a8380f84e1';  //企业secret 2562a38d1fa2b43ebff915a8380f84e1 e01fdbfb23b73a4f72f9525d52251e0c
//        $encryptedData = $code['encryptedData'];   //包括敏感数据在内的完整用户信息的加密数据
//        $jscode = $code['code'];   //用户登录授权获取到的code
//        //用code  获取sessionkey
//        $access_token = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $jscode . '&grant_type=authorization_code';
//        $result = $this->curlOpen($access_token);
//        $jsonarr = json_decode($result, true);
//        $sessionKey = $code['session_key'];
//        $iv = $code['iv'];
//        $pc = new \WXBizDataCrypt($appid, $sessionKey); //注意使用\进行转义
//        $errCode = $pc->decryptData($encryptedData, $iv, $data);
//        return json(array('k1' => $pc, 'k2' => $errCode, 'k3' => $data));
//
//    }

    public function search()
    {
        $data = input('get.');
//        return json($data);
//        jur: "4"
//s_id: "1180131231"
//username: "fht"
        $lsu = new Formcheck1();
        $username = $data['username'];
        $s_id = $data['s_id'];
        $data['jur']=intval($data['jur']);
        if ($data['jur'] == 4 || $data['jur'] == 5) {
//            return json($data);
            $res = $lsu->limit_select_user($data['username'], 'u_colle_view', 'collegeinfo', 'collegeinfo', $data['s_id']);
            return json($res);
        } elseif ($data['jur'] == 6) {
            $res = $lsu->limit_select_user($username, 'user_view', 'teacherinfo', 'u_name', $s_id);
        } elseif ($data['jur'] == 10) {
            $res = $lsu->limit_select_user($data['username'], 'u_apart_view', 'apartmentinfo', 'apartmentinfo', $data['s_id']);
//            return json(1);
        } elseif ($data['jur'] == 7) {
            $res = $lsu->limit_select_user($username, 'user_stu_view', 's_class', 's_class', $s_id);
        } elseif ($data['jur'] == 9) {
            $res = Db::name('stu_view')
                ->where('s_id', $data['s_id'])
                ->find();
        } elseif ($data['jur'] == 2||$data['jur'] == 3) {
            $res = Db::name('stu_view')
                ->where('s_id', $data['s_id'])
                ->find();
        } else {
            $res = "查询异常";
        }
        return json($res);
    }

    public function select()
    {
        $data = request()->param();   //获取当前请求的变量
        $res = Db::name($data['table'])
            ->where($data['Field'], $data['param'])
            ->find();
        return json($res);
    }

    public function scorefirst()
    {
        $res = Db::name('scorefirst')
            ->select();
        return json($res);
    }

    public function scoresec()//二级联动---二级分类
    {
        $scoresec = input('get.');
        $score = Db::name("scoresec_view")
            ->where('scorefirid', $scoresec['q'])
            ->select();
        $count = Db::name("scoresec")
            ->where('scorefirid', $scoresec['q'])
            ->count("scorefirid");
        return json($score);
    }

    public function operation()//学分操作后台
    {
        $date = input('get.');
        return json($date);
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
        $validate = validate([
            ['stuid', 'nrequire|regex:int|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
            ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
            ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
            ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类参数错误，请返回重试！'],
            ['opscoreclass', 'require|regex:int', '请选择操作类型！|操作类型参数错误，请返回重试！'],
            ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
        ]);
        $score1 = number_format($score['score']);//转字符为number类型
//        $date=array('opscoreclass'=>2,'score'=>10,'stuid'=>1180131231);
//        $score=0;
        if ($date['opscoreclass'] == '1' && ($score1 >= 100 || ($date['score'] + $score1) > 100)) {
            $score_update = Db::name('students')
                ->where('s_id', $date['stuid'])
                ->update(['score' => '100']);
            echo "<script type='text/javascript'>parent.layer.alert('德育学分已满分');parent.history.go(-1);</script>";
        } else if ($date['opscoreclass'] == '2' && ($score1 <= 0 || ($score1 - $date['score']) < 0)) {
            //            return json('进入减分判断');
            $score_update = Db::name('students')
                ->where('s_id', $date['stuid'])
                ->update(['score' => '0']);
            echo "<script type='text/javascript'>parent.layer.alert('德育学分已扣完');parent.history.go(-1);</script>";
        } else {
            if (!$validate->check($date)) {
                $msg = $validate->getError();
                echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
                exit;//判断数据是否合法
            } else {
                $scorenumcheck = Db::name("scoresec")
                    ->where('scoresecid', $date['opscoresec'])
                    ->find();
                if ($scorenumcheck['score'] >= $date['score']) {

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
                        echo "<script type='text/javascript'>parent.layer.alert('{$date["stuid"]}的学生操作已被确认！');parent.parent.parent.history.go(-1);</script>";
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('操作失败，请稍后再试！');parent.history.go(-1);</script>";
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('操作分数不能高于该操作分数上限！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function syslog()
    {
        $res = Db::name('zlog_view')
            ->limit(10)
            ->order('datetime desc')
            ->select();
        return json($res);
    }

//    public function examinelist() // 学分审核列表后台
//    {
//        $data=input('post.'); // 向指定的资源提交要被处理的数据（传递数据）
//        $count = Db::name("score_view") // 数据表名
//        ->where('opstate', '2') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//        ->count('id'); // 根据字段统计(学分审核列表总数)
//        // 分页查询
//        if ($data['jur']==4 || $data['jur']==5){ // 4=>分院院长 5=>分院团总支书记
//            $cate_list = Db::name("score_view") // 数据表名
//            ->limit(10) // limit方法则用于设置每页显示的数量
//            ->where('collegeid',$data['collegeid']) //同院
//            ->where('opstate', '2') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//            ->order('datetime desc') // order方法属于模型的连贯操作方法之一，用于对操作的结果排序（降序）
//            ->select(); // 查询数据集使用，select方法查询结果不存在，返回空数组
//        }elseif ($data['jur'] == 6){ // 6=>分院辅导员
//            $cate_list = Db::name("score_view") // 数据表名
//            ->limit(10) // limit方法则用于设置每页显示的数量
//            ->where('u_name',$data['teacherinfo']) //同辅导员
//            ->where('opstate', '2') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//            ->order('datetime desc') // order方法属于模型的连贯操作方法之一，用于对操作的结果排序（降序）
//            ->select(); // 查询数据集使用，select方法查询结果不存在，返回空数组
//        }
////        elseif ($data['jur'] == 10) {
////            $cate_list = Db::name("score_view")
////                ->limit(10)
////                ->where('apartmentid',$data['apartmentid'])
////                ->where('opstate', '2')//根据权限修改where条件
////                ->order('datetime desc')
////                ->select();
////        }
////        elseif ($data['jur'] == 7) {
////            $cate_list = Db::name("score_view")
////                ->limit(10)
////                ->where('s_class',$data['s_class'])
////                ->where('opstate', '2')//根据权限修改where条件
////                ->order('datetime desc')
////                ->select();
////        }
//        elseif ($data['jur'] == 9) {  // 9=>公寓管理中心
//            $cate_list = Db::name("score_view")  // 数据表名
//            ->limit(10)  // limit方法则用于设置每页显示的数量
//            ->where('apartmentinfo', 'not null') //公寓信息不为空
//            ->where('opstate', '2') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//            ->order('datetime desc') // order方法属于模型的连贯操作方法之一，用于对操作的结果排序（降序）
//            ->select(); // 查询数据集使用，select方法查询结果不存在，返回空数组
//        }elseif ($data['jur'] == 2) {  // 2=>学工处
//            $cate_list = Db::name("score_view")  // 数据表名
//            ->limit(10)  // limit方法则用于设置每页显示的数量
//            ->where('opstate', '2') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//            ->order('datetime desc') // order方法属于模型的连贯操作方法之一，用于对操作的结果排序（降序）
//            ->select(); // 查询数据集使用，select方法查询结果不存在，返回空数组
//        } else {
//            $res = "查询异常"; // 页面提示信息        //？？？
//        }
//        return json($cate_list); // 指定json数据输出
//    }

    public function examineonly(){
        $data=input('get.'); // 从指定的资源请求数据（获得数据）
        $res= Db::name('score_view')  // 数据表名
        ->where('id',$data['id']) // 用于规定选择的标准
        ->find(); // 查询一个数据使用，find方法查询结果不存在，返回null
        return json($res); // 指定json数据输出
    }

//    public function examinerun() // 学分审核
//    {
//        $data = input('post.'); // 向指定的资源提交要被处理的数据（传递数据）
//        $stateupdate = [ // 操作状态更新
//            'opstate' => '1', // 1=>已确认 2=>待审核
//            'info'=>'小程序',
//        ];
//        $ip = request()->ip(); // 获取发送请求的地址ip
//        $date = $data + $stateupdate; // 重新赋值
//        $checkclass = Db::table('scoreoperation') // 数据表名
//        ->where('opstate', '1') // 用于查询指定字段‘操作状态’ 1=>已确认 2=>待审核
//        ->where('id', $date['id']) // 用于规定选择的标准
//        ->select(); // 查询数据集使用，select方法查询结果不存在，返回空数组
//        //用户名重复性检测
//        if ($checkclass) {
//            return json("该操作已审核"); // 指定json数据输出
//        } else {
//            $checkusr = Db::table('user') // 数据表名
//            ->where('username', $date['username']) // 用于规定选择的标准
//            ->where('u_name', $date['othername']) // 用于规定选择的标准
//            ->select();
//            //用户名重复性检测
//            if ($checkusr) {
//                $time = date('Y-m-d H:i:s'); // 定义的时间字符串格式，默认的格式为 Y-m-d H:i:s
//                $editscore = Db::table('scoreoperation') // 数据表名
//                ->where('id', $date['id']) // 用于规定选择的标准
//                ->update([ // 更新数据表中的数据
//                    'opstate' => '1', // 字段‘操作状态’ 1=>已确认
//                    'othername' => $date['othername'], // 操作人信息
//                    'othertime' => $time, // 小程序学分审核操作时间
//                    'otherstate' => '1', // 字段‘操作状态’ 1=>已确认
//                    'info' => $date['info']]); // 小程序
//                if ($editscore) {
//                    $syslog = // 赋值
//                        [   'ip' => $ip, // ip地址
//                            'datetime' => $time = date('Y-m-d H:i:s'), // 操作时间
//                            'info' => '对学生操作流水号为为：' . $date['id'] . ' 进行了操作。', // 操作描述
//                            'state' => '重要', // 操作状态
//                            'username' => $usrlogo = $date['username'] // 用户名
//                        ];
//                    Db::table('systemlog')->insert($syslog); // 使用Db类的insert方法向数据库提交数据
//                    return json(array('操作成功','0',$editscore,$ip,$time,$date));  // 指定json数据输出
////                    exit; // 中断执行
//                } else {
//                    return json(array('参数错误','0',$editscore,$ip,$time,$date));  // 指定json数据输出
////                    exit; // 中断执行
//                    // 判断更新操作是否成功
//                }
//            } else {
//                return json(array('参数错误','0',$ip,$date)); // 指定json数据输出
////                exit; // 中断执行
//            }
//        }
//    }

    public function scoreoperationrun()//学分操作后台
    {
        $date = input('post.');
        if ($date['jur'] != 7) {//班长的话就翻译
            unset($date['jur']);
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
            $score1 = number_format($score['score']);//转字符为number类型
//        $date=array('opscoreclass'=>2,'score'=>10,'stuid'=>1180131231);
//        $score=0;
            if ($date['opscoreclass'] == '1' && ($score1 >= 100 || ($date['score'] + $score1) > 100)) {
                $score_update = Db::name('students')
                    ->where('s_id', $date['stuid'])
                    ->update(['score' => '100']);
                return json(array('德育学分已满分'));
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
//                    return json($data);
                    $scoreopartion = Db::table('scoreoperation')->insert($data);
                    if ($data['opscoreclass'] == '1') {
                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setInc('score', $date['score']);
                    } elseif($data['opscoreclass'] == '2') {
                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setDec('score', $date['score']);
                    }
//                    halt($opres);
                    if ($scoreopartion) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        return json(array($score['s_name'] . '的德育学分已被确认'));
                    } else {
                        return json(array('操作失败'));
                    }
                } else {
                    return json(array('参数异常'));
                }
            }
        } else if ($date['jur'] == 7) {
            unset($date['jur']);
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
            $scorenumcheck = Db::name("scoresec")
                ->where('scoresecid', $date['opscoresec'])
                ->find();
            if ($scorenumcheck['score'] >= $date['score']){
                $scoreopartion = Db::table('scoreoperation')->insert($data);
                if ($scoreopartion) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    return json(array('德育学分操作已被确认'));
                }
                else {
                    return json(array('操作失败'));
                }
            }
            else {
                return json(array('操作不能超过分数上线'));
            }
        } else {
            return json(array('参数异常'));
        }
    }
    public function sendadvice(){
        Db::name('advice')
            ->insert(request()->param());
    }

}