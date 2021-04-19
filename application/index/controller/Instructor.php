<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\model\User as UserModel;
use think\Exception;
use think\validate;
use think\Request;
use think\Env;
use think\View;
use think\Loader;
use app\index\controller\Formcheck;

//代码中具体分页代码及表格重载代码解释参照layui官方手册
class Instructor extends Controller//权限1
{
    protected function _initialize()
    {
        $usrname = session('username');
        if (empty($usrname)) {

            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "index" }, 5000);</script><span class="STYLE12">&nbsp;:(</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp检测到系统环境异常！系统将在5秒后正在自动跳转。<br>&nbsp&nbsp&nbsp&nbsp&nbsp您的操作已被中止，这可能是非法登陆或登陆超时导致，您可尝试重新登陆系统。<br/></body></html>';
            exit;
        } else {
            $result = Db::table('user')
                ->where('username', $usrname)
                ->where('jurisdiction', '6')
                ->where('state', '1')
                ->select();//通过session查询个人信息
            if ($result == false) {
                session('username', null);
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "index" }, 5000);</script><span class="STYLE12">&nbsp;:(</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp检测到账户异常！系统将在5秒后自动跳转<br>&nbsp&nbsp&nbsp&nbsp&nbsp您的操作已被中止，这可能是权限不足或您的账户信息已被管理员修改，您可尝试重新登陆系统。<br/></body></html>';
                exit;
            }
        }
    }

    public function hddy()//首页左边栏
    {
        $result = Db::table('system')
            ->where('id', '1')
            ->find();//通过session查询个人信息
        $result1 = Db::name('score_view')->where('opstate','2')->count();
        $this->assign('data1',$result1);
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function log()//首页右边内容
    {
        return $this->fetch();
    }

    public function goout()//退出
    {
        session('username', null);
        $ip="http://".session('ip');
        echo "<script>window.parent.location.href='$ip'</script>>";
    }

//个人管理模块开始部分-------------------------------------------------------------》

    public function information()//个人信息页
    {
        $usrname = session('username');
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->select();//通过session查询个人信息
        $rs1 = json($result);
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function informationmodify()//个人信息修改处理
    {
        $date = input('post.');
        $validate = new validate([
            ['add', 'require|length:11|regex:int', '手机号码不能为空|手机号码限制为11位|手机号码限制全部为数字'],
            ['u_mail', 'email', '邮箱格式不正确'],
            ['qq', 'regex:int|min:5|max:11', 'QQ号码限制全部为数字|QQ号码限制5-11位|QQ号码限制5-11位'],
            ['vx', 'min:5|max:20|alphaDash|regex:fst-a', '微信号码至少5位|微信号码限制不能超过20位|微信号包含非法字符！|微信号必须以字母开头'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改个人信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $cd=new Formcheck();
            $checkey=array('add','qq','u_mail','vx');
            $cd_res=$cd->check_stuinfo($date,'user',$checkey,'username');
//            var_dump($cd_res);
            if ($cd_res){
                $err_msg=$cd_res['msg'];
                echo "<script>parent.layer.alert('$err_msg');self.location=document.referrer;</script>";
                exit;
            }
            $username = session('username');
            if ($username === $date['username']) {//判断当前用户名是否和session相等，预防通过前端修改用户名
                Db::table('user')
                    ->where('username', $username)
                    ->update([
                        'add' => $date['add'],
                        'u_mail' => $date['u_mail'],
                        'qq' => $date['qq'],
                        'vx' => $date['vx']]);//修改操作
                if ($this) {
                    $syslog = [
                        'ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '修改了个人信息。',
                        'state' => '正常',
                        'username' => $usrlogo = session('username'),
                    ];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script>parent.layer.alert('修改成功！');self.location=document.referrer;;</script>";
                    exit;
                } else {
                    echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                exit;
            }
        }
    }

    public function respwd()//修改密码页面
    {
        return $this->fetch();
    }

    public function respwdrun()//验证密码操作
    {
        $date = input('post.');
        $validate = new validate([
            ['password', 'require|min:5|max:20|alphaDash', '密码不能为空|密码至少5位|密码不能超过20位|密码不能包含非法字符'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $usrname = session('username');
            $user = new  UserModel();
            if ($usrname === $date['username']) {//判断当前用户名是否和session相等，预防通过前端修改用户名
                $result = $user->where('username', $date['username'])->find();
                if ($result) {
                    if ($result['password'] === md5($date['password'])) {
                        echo '<script>window.location="newpwd";</script>';
                    } else {
                        echo "<script>parent.layer.alert('原密码验证失败，请返回重试！');self.location=document.referrer;;</script>";
                        exit;
                    }
                }
            } else {
                echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                exit;
            }
        }
    }

    public function newpwd()//设置新密码页面
    {
        return $this->fetch();
    }

    public function newpwdrun()//设置新密码操作
    {
        $date = input('post.');
        $validate = new validate([
            ['password', 'require|min:5|max:20|alphaDash', '密码不能为空|密码至少5位|密码不能超过20位|密码不能包含非法字符'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改个人密码时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $usrname = session('username');
            if ($usrname === $date['username']) {//判断当前用户名是否和session相等，预防通过前端修改用户名
                if ($date['password'] === $date['passwordd']) {//验证密码一致性
                    Db::table('user')
                        ->where('username', $usrname)
                        ->update([
                            'password' => md5($date['password'])]);//修改操作
                    if ($this) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '修改个人密码。',
                            'state' => '正常',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        $ip="http://".session('ip');
                        session('username', null);
//                        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "index" }, 3000);</script><span class="STYLE12">&nbsp;:)</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp密码修改成功！系统正在自动跳转至登陆页面。<br/></body></html>';
                        echo "<script>window.parent.location.href='$ip'</script>>";
                        exit;

                    } else {
                        echo "<script>parent.layer.alert('修改失败，请返回重试！');self.location=document.referrer;;</script>";
                    }

                } else {
                    echo "<script>parent.layer.alert('密码不一致，请返回重试！');self.location=document.referrer;;</script>";
                }

            } else {
                echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
            }
        }
    }

    public function personallog()//个人操作日志页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看个人操作日志。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function personalloglist()//个人操作日志列表后台
    {
        $usrname = session('username');
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('username', $usrname)
            ->count("id");
        $cate_list = Db::name("score_view")
            ->limit($start, $limit)
            ->where('username', $usrname)
            ->order('id desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function personallogcheck(Request $request)//个人操作日志查询重载
    {
        $usrname = session('username');
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('username', $usrname)
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('username', $usrname)
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->limit($start, $limit)
            ->order("id desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

    public function showpersonallog()//个人操作日志查看操作
    {
        $date = input('get.');
        $validate = new validate([
            ['id', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '疑似在查看个人操作日志详情时篡改页面信息。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('score_view')
                ->where('id', $date['id'])
                ->find();//通过session查询个人信息

            $this->assign('data', $result);
            return $this->fetch();
        }

    }

    public function editpersonallog()//学分操作编辑页面
    {
        $usrname = session('username');
        $date = input('get.');
        $validate = new validate([
            ['id', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '疑似在查看个人操作日志详情(编辑)时篡改页面信息。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $classid = Db::table('user')->where('username', $usrname)->value('u_classinfo');
            $result = Db::table('score_view')
                ->where('id', $date['id'])
                ->where('u_classinfo', $classid)
                ->find();//通过session查询个人信息
            $result1 = Db::table('user')
                ->where('username', $usrname)
                ->find('u_name,username');//通过session查询个人信息
            $this->assign('data1', $result1);
            $this->assign('data', $result);
            return $this->fetch();
        }
    }

    public function editpersonallogrun()//学分编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['opstate', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],

        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                ->where('opstate', '4')
                ->where('id', $date['id'])
                ->select();//用户名重复性检测
            if ($checkclass) {
                echo "<script>parent.layer.alert('该操作已被撤销，请勿重复提交相同操作！');self.location=document.referrer;</script>";
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
                            'opstate' => '3',
                            'othername' => $date['othername'],
                            'othertime' => $time,
                            'otherstate' => '3',
                            'info' => $date['info']]);//修改操作
                    if ($editscore) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '操作了流水号：' . $date['id'] . ' 的学分操作，申请撤销。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        echo "<script>parent.layer.alert('保存成功！');self.location=document.referrer;;</script>";
                        exit;
                    } else {
                        echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                        exit;//判断更新操作是否成功
                    }
                } else {
                    echo "<script>parent.layer.alert('参数错误！');self.location=document.referrer;;</script>";
                    exit;
                }
            }
        }
    }


    //学生管理模块开始部分--------------------------------------------------------------》
    public function dormitorymore()//添加公寓寝室类别联动
    {
        $date = input('get.');
        $dormitorydata = Db::name("dormitory")
            ->where("apartmentid", $date['r'])
            ->select();
        echo "<option value=''>未选择</option>";
        foreach ($dormitorydata as $value) {
            echo "<option value='{$value['dormitoryid']}'>{$value['dormitoryinfo']}</option>";
        }
    }

    public function addstu()//添加学生页面
    {
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];

        $result = Db::table('class')
            ->where('collegeid', $usrcollege)
            ->order("class desc")
            ->select();
        $result2 = Db::table('apartment')
            ->order('apartmentid desc')
            ->select();
        //$rs1=json($result);
        $this->assign('data1',$result2);
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function classmore()//选择行政班以后学院等更多信息显示
    {

        $classmore = input('get.');
        $classinfo = Db::name("class_view")
            ->where('class', $classmore['q'])
            ->find();
        echo "<td >辅导员：</td>
               <td >
                  " . $classinfo['teacherinfo'] . "
               </td>
               <td >所属专业：</td>
               <td >
                 " . $classinfo['majorinfo'] . "
               </td>
               <td >所在学院：</td>
               <td>
               " . $classinfo['collegeinfo'] . "
          </td>";
    }

    public function addsturun()//添加学生后台操作
    {
        $date = input('post.');
        $validate = new validate([
            ['s_id', 'require|regex:int|min:10|max:15', '学号不能为空！|学号限制全部数字！|学号至少10位！|学号输入过长！'],
            ['s_name', 'require|chs|max:15', '姓名不能为空！|姓名只能为5位以内的汉字！|姓名只能为5位以内的汉字！'],
            ['s_sex', 'require|chs|max:3', '性别不能为空！|性别参数异常，请返回重试！|性别参数异常，请返回重试！'],
            ['s_proid', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空！|身份证号码限制18位数字，最后一位可以为X！|身份证号码限制不能超过18位！'],
            ['s_add', 'length:11|regex:int', '学生手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_home', 'max:40', '家庭住址限制20个字符以内'],
            ['s_class', 'require|regex:int|max:10', '未选择班级！|班级参数异常，请返回重试！|班级参数异常，请返回重试！'],
            ['s_room', 'require|max:10|alphaDash|regex:room', '寝室信息不能为空！|寝室信息输入过长！|寝室信息包含非法字符！|寝室号及床位号格式必须为5110-1'],
            ['s_dadname', 'max:5|chs', '父亲姓名至多输入5个汉字|父亲姓名限制为全汉字'],
            ['s_dadadd', 'length:11|regex:int', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_mumname', 'max:5|chs', '母亲姓名至多输入5个汉字|母亲姓名限制为全汉字'],
            ['s_mumadd', 'length:11|regex:int', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_dormitory', 'require', '寝室号不能为空！'],
        ]);
//        $date['s_room']=$date['s_dormitory']."-".$date['s_room'];
//        foreach ($date as $key=>$value)
//        {
//            try{
//                $res=Db::name('students')
//                    ->where('s_id|s_name|s_sex|s_proid|s_add|s_home|s_class|s_room|s_apartment|s_dormitory|s_dadname|s_dadadd|s_mumname|s_mumadd',$value)
//                    ->findOrFail();
////                $res=Db::name('students')
////                    ->where('s_id','')
////                    ->findOrFail();//这个操作在遇到查询失败=数据库中没有重复的信息的时候会遵循trycatch抛出异常
//                echo "<script>parent.layer.alert('请检查添加的学生信息防止重复');self.location=document.referrer;</script>";
//                exit;
//            }catch(\Exception	$e){
//               break;
//            }
//        }
//        return json("continue");
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学生时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            halt(1);
            exit;//判断数据是否合法
        } else {
            $cd=new Formcheck();
            $checkey=array('s_id','s_add','s_proid','s_room');
            $cd_res=$cd->check_addstu($date,'students',$checkey);
            if ($cd_res['code']==1){
                $err_msg=$cd_res['msg'];
                echo "<script>parent.layer.alert('$err_msg');self.location=document.referrer;</script>";
                exit;
            }
            $result = Db::table('students')
                ->where('s_id', $date['s_id'])
                ->select();//用户名重复性检测
            if ($result) {
                echo "<script>parent.layer.alert('该学生信息已经存在，请返回重试！');self.location=document.referrer;;</script>";
            } else {
                Db::table('students')->insert($date);
                if ($this) {

                    echo "<script>parent.layer.alert('学生信息添加成功！');self.location=document.referrer;;</script>";
                } else {
                    echo "<script>parent.layer.alert('学生信息添加失败！');self.location=document.referrer;;</script>";
                }
            }
        }
    }

    public function addmanystu()//批量添加学生页面
    {
        $result = Db::table('class_view')
            ->select();
        $this->assign('data', $result);
        return $this->fetch();

    }

    public function addmanysturun()//批量添加学生后台
    {
        $request = \think\Request::instance();
        vendor("PHPExcel.PHPExcel");//引入导入excel第三方库

        $file = request()->file('fileUpload');

        if (empty($file)) {
            $this->error("导入数据失败，可能是数据为空");//数据为空返回错误
        }
        $info = $file->validate(['ext' => 'xlsx,xls'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info == false) {
            $this->error("导入数据失败，可能是文件格式或文件类型导致");//数据为空返回错误
        }
        //获取上传到后台的文件名
        $fileName = $info->getSaveName();
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '上传文件批量导入学生信息，文件名为：' . $fileName . '',
            'state' => '重要',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        //获取文件路径
        $filePath = 'uploads' . DS . $fileName;
        //   部署在linux环境下时获取路径需要更改为绝对路径 
        //  509：$filePath = 'uploads'.DS.$fileName;
//        echo dirname(dirname(dirname(__FILE__)));
//        exit;
        //获取文件后缀
        $suffix = $info->getExtension();
        //判断哪种类型
        if ($suffix == "xlsx") {
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        } else {
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
        }

        //载入excel文件
        $excel = $reader->load("$filePath", $encode = 'utf-8');
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num = $sheet->getHighestRow();
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $data = []; //数组形式获取表格数据

        for ($i = 2; $i <= $row_num; $i++) {
            // var_dump($sheet->getCell("A".$i)->getValue());exit;
            $data['s_id'] = $sheet->getCell("A" . $i)->getValue();
            $data['s_name'] = $sheet->getCell("B" . $i)->getValue();
            $data['s_proid'] = $excel->getActiveSheet()->getCell("C" . $i)->getValue();
            $data['s_sex'] = $excel->getActiveSheet()->getCell("D" . $i)->getValue();
            $data['s_class'] = $excel->getActiveSheet()->getCell("E" . $i)->getValue();
            $data['s_room'] = $excel->getActiveSheet()->getCell("F" . $i)->getValue();
            $data['s_add'] = $excel->getActiveSheet()->getCell("G" . $i)->getValue();
            $data['apartment'] = $excel->getActiveSheet()->getCell("H".$i)->getValue();
            $data['dormitory'] = $excel->getActiveSheet()->getCell("I".$i)->getValue();
            $classcheck = Db::name("class")
                ->where('class', $data['s_class'])
                ->select();
            if ($classcheck == false) {
                $this->error("文件中第 {$i} 行的班级：{$data['s_class']} 无法在系统中被找到，请核对重试。");//数据为空返回错误
                exit;
            }
            $sidcheck = Db::name("students")
                ->where('s_id', $data['s_id'])
                ->select();
            if ($sidcheck) {
                $this->error("文件中第 {$i} 行的学生学号：{$data['s_id']} 学生信息已经存在，请核对后重试。");//数据为空返回错误
                exit;
//                Db::table('students')->insert($data);
            }
            $sidcheck = Db::name("students")
                ->where('s_id', $data['s_id'])
                ->select();
            if ($sidcheck) {
                $this->error("文件中第 {$i} 行的学生学号：{$data['s_id']} 学生信息已经存在，请核对后重试。");//数据为空返回错误
                exit;
            }
            $aidcheck = Db::name("apartment")
                ->where('apartmentinfo', $data['apartment'])
                ->select();
            if(!$aidcheck){
                $this->error("文件中第{$i}行的学生对应的公寓楼号：{$data['apartment']}信息不存在，请核对后重试。");
                exit;
            }
            $didcheck = Db::name("dormitory")
                ->where("dormitoryinfo",$data['dormitory'])
                ->select();
            if(!$didcheck){
                $this->error("文件中第{$i}行的学生对应的寝室号：{$data['dormitory']}信息不存在，请核对后重试。");
                exit;
            }
        }
        for ($i = 2; $i <= $row_num; $i++) {
            // var_dump($sheet->getCell("A".$i)->getValue());exit;
            $data['s_id'] = $sheet->getCell("A" . $i)->getValue();
            $data['s_name'] = $sheet->getCell("B" . $i)->getValue();
            $data['s_proid'] = $excel->getActiveSheet()->getCell("C" . $i)->getValue();
            $data['s_sex'] = $excel->getActiveSheet()->getCell("D" . $i)->getValue();
            $data['s_class'] = $excel->getActiveSheet()->getCell("E" . $i)->getValue();
            $data['s_room'] = $excel->getActiveSheet()->getCell("F" . $i)->getValue();
            $data['s_add'] = $excel->getActiveSheet()->getCell("G" . $i)->getValue();
            $data['apartment'] = $excel->getActiveSheet()->getCell("H".$i)->getValue();
            $data['dormitory'] = $excel->getActiveSheet()->getCell("I".$i)->getValue();
            $data['s_apartment'] = Db::table('apartment')->where('apartmentinfo',$data['apartment'])->value('apartmentid');
            $data['s_dormitory'] = Db::table('dormitory')->where('dormitoryinfo', $data['dormitory'])->value('dormitoryid');
            $gomany = Db::table('students')->insert($data);
            if ($gomany == false) {
                $this->error("发生未知错误，请联系管理员");//数据为空返回错误
                exit;
            }

        }
        $num = $row_num - 1;
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '上传文件批量导入了：' . $num . ' 条学生信息，文件名为：' . $fileName . '',
            'state' => '重要',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        $this->success("共 {$num} 条学生信息导入成功！");

        if ($this) {
            echo "<tr>";
            for ($i = 2; $i <= $row_num; $i++) {
                // var_dump($sheet->getCell("A".$i)->getValue());exit;
                $data['s_id'] = $sheet->getCell("A" . $i)->getValue();
                $data['s_name'] = $sheet->getCell("B" . $i)->getValue();
                $data['s_proid'] = $excel->getActiveSheet()->getCell("C" . $i)->getValue();
                $data['s_sex'] = $excel->getActiveSheet()->getCell("D" . $i)->getValue();
                $data['s_class'] = $excel->getActiveSheet()->getCell("E" . $i)->getValue();
                $data['s_room'] = $excel->getActiveSheet()->getCell("F" . $i)->getValue();
                $data['s_add'] = $excel->getActiveSheet()->getCell("G" . $i)->getValue();
                $data['apartment'] = $excel->getActiveSheet()->getCell("H".$i)->getValue();
                $data['dormitory'] = $excel->getActiveSheet()->getCell("I".$i)->getValue();
                echo "<td> " . $data['s_id'] . " " . $data['s_name'] . " " . $data['s_proid'] . " "
                    . $data['s_sex'] . " " . $data['s_class'] . " " . $data['s_room'] . " "
                    . $data['s_add']." " . $data['apartment'] . " " . $data['dormitory'] ."</td>";
            }
            echo "</tr>";
        } else {
            echo "<script>parent.layer.alert('数据导入失败，请返回重试！');self.location=document.referrer;;</script>";
        }
    }

    public function showstu()//学生查询页面
    {

        return $this->fetch();
    }

    public function stulist()//学生查询列表后台
    {
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];

        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $username = Db::table('user')->where('username', $usrname)->value('u_name');
        $count = Db::name("stu_view")
            //
            ->where('collegeid', $usrcollege)
            ->where('teacherinfo', $username)
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            //->where('teacherinfo',$username)
            ->where('collegeid', $usrcollege)
            ->where('teacherinfo', $username)
            ->limit($start, $limit)
            ->order('score desc')->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }

        return json($list);
    }

    public function stucheck(Request $request)//学生查询列表重载
    {
        $date = $request->post();
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];
        $username = Db::table('user')->where('username', $usrname)->value('u_name');
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询

        $count = Db::name("stu_view")
            ->where('teacherinfo', $username)
            ->where('collegeid', $usrcollege)
            ->where('s_name|s_id|class|teacherinfo|apartmentinfo|dormitoryinfo', 'like', "%" . $date["s_name"] . "%")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            ->where('teacherinfo', $username)
            ->where('collegeid', $usrcollege)
            ->where('s_name|s_id|class|teacherinfo|apartmentinfo|dormitoryinfo', 'like', "%" . $date["s_name"] . "%")
            ->limit($start, $limit)
            ->order("s_id desc")
            ->select();
        //学号与姓名模糊查询
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";
        }
        return json($list);//返回数据给前端
    }

    public function showstuinfo()//查看学生信息
    {
        $date = input('get.');
        $result1 = Db::name("stu_view")
            ->where('s_id', $date["id"])
            ->find();
        $result2 = Db::table('score_view')
            ->where('s_id', $date["id"])
            ->order('datetime','desc')
            ->select();
        $this->assign('data', $result1); //返回学生信息
        $this->assign('data1', $result2); //返回学分信息数组供循环
        return $this->fetch();
    }

    public function editstuinfo()//学生信息编辑页面
    {
        $date = input('get.');
        $result1 = Db::name("stu_view")
            ->where('s_id', $date["id"])
            ->find();
        $result2 = Db::table('class')
            ->select();
        $this->assign('data', $result1);
        $this->assign('data1', $result2);
        return $this->fetch();
    }

    public function editstuinforun()//学生编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['s_id', 'require|regex:int|min:10|max:15', '学号不能为空！|学号限制全部数字！|学号至少10位！|学号输入过长！'],
            ['s_name', 'require|chs|max:15', '姓名不能为空！|姓名只能为5位以内的汉字！|姓名只能为5位以内的汉字！'],
            ['s_sex', 'require|chs', '性别不能为空！|性别参数异常！'],
            ['s_proid', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空！|身份证号码限制18位数字，最后一位可以为X！|身份证号码限制不能超过18位！'],
            ['s_add', 'length:11|regex:int', '学生手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_home', 'max:60', '家庭住址限制20个字符以内'],
            ['s_class', 'require|regex:int|max:10', '未选择班级！|班级参数异常，请返回重试！|班级参数异常，请返回重试！'],
            ['s_room', 'require|max:10|alphaDash|regex:room', '寝室信息不能为空！|寝室信息输入过长！|寝室信息包含非法字符！|寝室号及床位号格式必须为5110-1'],
            ['s_dadname', 'max:5|chs', '父亲姓名至多输入5个汉字|父亲姓名限制为全汉字'],
            ['s_dadadd', 'length:11|regex:int', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_mumname', 'max:5|chs', '母亲姓名至多输入5个汉字|母亲姓名限制为全汉字'],
            ['s_mumadd', 'length:11|regex:int', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改学号为：' . $date['s_id'] . ' 的信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $cd=new Formcheck();
            $checkey=array('s_id','s_add','s_proid','s_room');
            $cd_res=$cd->check_stuinfo($date,'students',$checkey,'s_id');
//            var_dump($cd_res);
            if ($cd_res){
                $err_msg=$cd_res['msg'];
                echo "<script>parent.layer.alert('$err_msg');self.location=document.referrer;</script>";
                exit;
            }
            Db::table('students')
                ->where('s_id', $date['s_id'])
                ->update([
                    's_dadname' => $date['s_dadname'],
                    's_dadadd' => $date['s_dadadd'],
                    's_sex' => $date['s_sex'],
                    's_mumname' => $date['s_mumname'],
                    's_mumadd' => $date['s_mumadd'],
                    's_name' => $date['s_name'],
                    's_proid' => $date['s_proid'],
                    's_add' => $date['s_add'],
                    's_home' => $date['s_home'],
                    's_class' => $date['s_class'],
                    's_room' => $date['s_room'],
                ]);//修改操作
            if ($this) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '修改学号为：' . $date['s_id'] . ' 的信息。',
                    'state' => '重要',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script>parent.layer.alert('保存成功！');self.location=document.referrer;;</script>";
                exit;
            } else {
                echo "<script>parent.layer.alert('保存参数错误，请返回重试！');self.location=document.referrer;;</script>";
                exit;//判断更新操作是否成功
            }
        }
    }


//学分操作模块开始-------------------------------------------------------------------》
    public function scoreoperation()//学分操作首页页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '进入学分操作页面。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function scoreoperationlist()//初始化学分操作页面表格
    {
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = "0";
        return json($list);
    }

    public function scoreoperationcheck(Request $request)//学分操作页面表格重载
    {
        $date = $request->post();
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];
        $username = Db::table('user')->where('username', $usrname)->value('u_name');
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("stu_view")
            ->where('collegeid', $usrcollege)
            ->where('teacherinfo', $username)
            ->where('s_id|s_name', 'like', "%" . $date["stuname"] . "%")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            ->where('collegeid', $usrcollege)
            ->where('teacherinfo', $username)
            ->where('s_id|s_name', 'like', "%" . $date["stuname"] . "%")
            ->limit($start, $limit)
            ->order("s_id desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";
        }
        return json($list);//返回数据给前端 
    }

    public function scoreshowstu()//学分页面查看学生进行操作页面
    {
        $stu = input('get.');
        $usrname = session('username');
        if (empty($usrname)) {
            echo "<h3>非法登陆或登陆超时，请返回重新登陆！</h3>";
            exit;
        } else {
            $classid = Db::table('user')->where('username', $usrname)->value('u_classinfo');
            $result1 = Db::name("user_view")
                ->where('username', $usrname)
                ->find();//使用find前端可以直接输出
            $this->assign('data', $result1);
            $result2 = Db::name("stu_view")
                ->where('s_id', $stu["id"])
                ->find();//使用find前端可以直接输出
            $this->assign('data2', $result2);
            $result3 = Db::name("scorefirst")
                ->where('collegeid', $classid)
                ->whereOr('collegeid',3)
                ->select();
            $this->assign('data3', $result3);
            return $this->fetch();
        }

    }

    public function scoresec()//二级联动---二级分类
    {
        $usrname = session('username');
        //$classid = Db::table('user')->where('username',$usrname)->value('u_classinfo');
        $scoresec = input('get.');
        $score = Db::name("scoresec_view")
            ->where('scorefirid', $scoresec['q'])
            //->where('u_classinfo',$classid)
            ->select();
        $count = Db::name("scoresec")
            ->where('scorefirid', $scoresec['q'])
            // ->where('u_classinfo',$classid)
            ->count("scorefirid");
        return json($score);
        echo "<select name='opscoresec'>";

        foreach ($score as $value) {
            echo "<option value='{$value['scoresecid']}' name='opscoresec'>{$value['scoresecinfo']} 分数上限：{$value['score']}</option>11";
        }
        echo "</select>";
    }

//    public function scoreoperationrun()//学分操作后台
//    {
//        $date = input('post.');
//        if($date['opscoreclass']=="加分"){
//            $date['opscoreclass']='1';
//        }else if($date['opscoreclass']=="减分"){
//            $date['opscoreclass']='2';
//        }
//        $time = date('Y-m-d H:i:s');
//        $ip = request()->ip();
//        $operinfo = [
//            'ip' => $ip,
//            'datetime' => $time,
//            'opstate' => '1',
//            'otherstate' => '0',
//        ];
//        $data = $date + $operinfo;
//        $validate = new validate([
//            ['stuid', 'require|regex:int|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
//            ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
//            ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
//            ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类错误，请返回重试！'],
//            ['opscoreclass', 'require|regex:int', '请选择操作类型！|操作类型参数错误，请返回重试！'],
//            ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
//        ]);
//        if (!$validate->check($date)) {
//            $msg = $validate->getError();
//            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
//            exit;//判断数据是否合法
//        } else {
//
//            $scorenumcheck = Db::name("scoresec")
//                ->where('scoresecid', $date['opscoresec'])
//                ->find();
//            if ($scorenumcheck['score'] >= $date['score']) {
//
//                $scoreopartion = Db::table('scoreoperation')->insert($data);
//                if ($data['opscoreclass'] == '1') {
//                    $opres = Db::table('students')->where('s_id', $date['stuid'])->setInc('score', $date['score']);
//                } else {
//                    $opres = Db::table('students')->where('s_id', $date['stuid'])->setDec('score', $date['score']);
//                }
//                if ($scoreopartion) {
//                    $syslog = ['ip' => $ip = request()->ip(),
//                        'datetime' => $time = date('Y-m-d H:i:s'),
//                        'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
//                        'state' => '重要',
//                        'username' => $usrlogo = session('username'),];
//                    Db::table('systemlog')->insert($syslog);
//                    $this->success("对学号：{$date['stuid']} 的学生操作已被确认！");
//                } else {
//                    echo "<script>parent.layer.alert('操作失败，请稍后再试！');self.location=document.referrer;;</script>";
//                }
//            } else {
//                echo "<script>parent.layer.alert('操作分数不能高于该操作分数上限！');self.location=document.referrer;;</script>";
//            }
//        }
//    }

    //学分操作区域----------------------------------》开始

    public function scoreOperRange($opscoresec, $score)
    {
        return number_format(Db::name("scoresec")->where('scoresecid', $opscoresec)->value('score')) >= $score;
    }

    public function scoreoper($stuid, $score, $opscoreclass)
    {
        //获取当前学生的分数
//        halt($opscoreclass);
//        $score = number_format(Db::name('students')->where('s_id', $stuid)->value('score'));
        if ($this->exchg[$opscoreclass]) {
            Db::name('students')->where('s_id', $stuid)->setInc('score',$score);//先加分
            //再判断界限
            if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) > 100) {
                //保持临界值
                Db::name('students')->where('s_id', $stuid)->update(['score' => '100']);
                echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最高100分');self.location=document.referrer;;</script>";
                exit();
            };
        } elseif (!$this->exchg[$opscoreclass]) {
            Db::name('students')->where('s_id', $stuid)->setDec('score',$score);//先减分
            //再判断界限
            if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) < 0) {
                //保持临界值
                Db::name('students')->where('s_id', $stuid)->update(['score' => '0']);
                echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最低0分');self.location=document.referrer;;</script>";
                exit();
            }
        }
    }

    private $exchg = [
        '加分' => true,
        '减分' => false
    ];
    private $ls_exchg=[
        '加分'=>'1',
        '减分'=>'2'
    ];
    public function scoreoperationrun()//学分操作后台
    {
        //接收数据
        $date = input('post.');
        //加减分转换
//        dump($date['opscoreclass']);
//        halt($date['opscoreclass']);
//        $time = date('Y-m-d H:i:s');
//        $ip = request()->ip();
        //查询学生学分用于学分上限判断
        $score = Db::name('students')
            ->where('s_id', $date['stuid'])
            ->find();
        //准备操作参数数据用于插入学分操作表
        $operinfo = [
            'ip' => request()->ip(),
            'datetime' => date('Y-m-d H:i:s'),
            'opstate' => '1',
            'otherstate' => '0',
        ];
        $data = $date + $operinfo;
        $validate = new validate([
            ['stuid', 'require|regex:int|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
            ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
            ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
            ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类错误，请返回重试！'],
            ['opscoreclass', 'require', '请选择操作类型！'],
            ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
        ]);
        $score1 = number_format($score['score']);//转字符为number类型
//        $date=array('opscoreclass'=>2,'score'=>10,'stuid'=>1180131231);
//        $score=0;
        //操作分数后是否超出限制
//        if ($date['opscoreclass']=='1'&&($score1>=100||($date['score']+$score1)>100)){
//            $score_update=Db::name('students')
//                ->where('s_id',$date['stuid'])
//                ->update(['score'=>'100']);
//            echo "<script type='text/javascript'>parent.layer.alert('德育学分已满分');self.location=document.referrer;;</script>";
//        }else if ($date['opscoreclass']=='2'&&($score1<=0||($score1-$date['score'])<0)){
//    //            return json('进入减分判断');
//            $score_update=Db::name('students')
//                ->where('s_id',$date['stuid'])
//                ->update(['score'=>'0']);
//            echo "<script type='text/javascript'>parent.layer.alert('德育学分已扣完');self.location=document.referrer;;</script>";
//        }
//        else {
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            //操作分数是否超出限制
//                $scorenumcheck = Db::name("scoresec")
//                    ->where('scoresecid', $date['opscoresec'])
//                    ->find();
//                if ($scorenumcheck['score'] >= $date['score']) {
//
//                    if ($data['opscoreclass'] == '1') {
//                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setInc('score', $date['score']);
//                    } else {
//                        $opres = Db::table('students')->where('s_id', $date['stuid'])->setDec('score', $date['score']);
//                    }
            if ($this->scoreOperRange($date['opscoresec'], $date['score'])) {
                $scoreopartion = $this->scoreoper($date['stuid'], $date['score'], $date['opscoreclass']);
            }elseif (!$this->scoreOperRange($date['opscoresec'], $date['score'])){
                echo "<script type='text/javascript'>parent.layer.alert('学分操作超出限制');self.location=document.referrer;;;;</script>";
                exit();
            }
            $data['opscoreclass'] = $this->ls_exchg[$data['opscoreclass']];
            $scoreopartion = Db::table('scoreoperation')->insert($data);
            if ($scoreopartion) {
                //更新系统操作日志
                $syslog = ['ip' => request()->ip(),
                    'datetime' => date('Y-m-d H:i:s'),
                    'info' => '对学生学号为：' . $date['stuid'] . ' 进行学分操作。',
                    'state' => '重要',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                //$this->success("对学号：{$date['stuid']} 的学生操作已被确认！");
                echo "<script type='text/javascript'>parent.layer.alert('{$date["stuid"]}的学生操作已被确认！');self.location=document.referrer;;;;</script>";
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('操作失败，请稍后再试！');self.location=document.referrer;;</script>";
            }
//        else {
//                echo "<script type='text/javascript'>parent.layer.alert('操作分数不能高于该操作分数上限！');self.location=document.referrer;;</script>";
////                }
//            }
        }
    }
    //学分操作区域----------------------------------》结束

    public function examine()//待审核操作页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '进入审核学分操作页面。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function examinelist()//待审核操作列表后台
    {
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];

        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('collegeid', $usrcollege)
            ->where('opstate', '2')//根据权限修改where条件
            ->count("id");
        $cate_list = Db::name("score_view")
            ->limit($start, $limit)
            ->where('collegeid', $usrcollege)
            ->where('opstate', '2')//根据权限修改where条件
            ->order('datetime desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($list);
    }

    public function examineload(Request $request)//待审核操作列表重载
    {
        $date = $request->post();
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('collegeid', $usrcollege)
            ->where('opstate', '2')//根据权限修改where条件
            ->where('id|s_id|s_name|scoresecinfo', 'like', "%" . $date["id"] . "%")
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('opstate', '2')//根据权限修改where条件
            ->where('collegeid', $usrcollege)
            ->where('id|s_id|s_name|scoresecinfo', 'like', "%" . $date["id"] . "%")
            ->limit($start, $limit)
            ->order("datetime desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($list);//返回数据给前端 
    }

    public function showexamine()//查看待审核操作详情页面
    {
        $date = input('get.');
        $validate = new validate([
            ['id', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('score_view')
                ->where('id', $date['id'])
                ->find();//通过session查询个人信息

            $this->assign('data', $result);
            return $this->fetch();
        }
    }

    public function ExaminScoreOper($stuid, $score, $opscoreclass)
    {
        //获取当前学生的分数
//        halt(array($stuid,$score,$opscoreclass));
        try {
            if ($this->exchg2[$opscoreclass]) {
                Db::name('students')->where('s_id', $stuid)->setInc('score',$score);//先加分
                //再判断界限
                if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) > 100) {
                    //保持临界值
                    Db::name('students')->where('s_id', $stuid)->update(['score' => '100']);
                    echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最高100分');self.location=document.referrer;;</script>";
                    exit();
                };
            } elseif (!$this->exchg2[$opscoreclass]) {
                Db::name('students')->where('s_id', $stuid)->setDec('score',$score);//先减分
                //再判断界限
                if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) < 0) {
                    //保持临界值
                    Db::name('students')->where('s_id', $stuid)->update(['score' => '0']);
                    echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最低0分');self.location=document.referrer;;</script>";
                    exit();
                }
            }
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    private $exchg2=[
        1=>true,
        2=>false
    ];

    public function examinerun()//审核操作
    {
        $data = input('post.');
        $stateupdate = [
            'opstate' => '1',
        ];//#########################################根据权限需要修改一下代码块的相关代表状态的参数
        $date = $data + $stateupdate;
        $validate = new validate([
            ['opstate', 'require|regex:int', '请选择操作类型！|操作当前状态参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['s_id', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['classinfo', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['score', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {

            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                ->where('id', $date['id'])
                ->find();
            if ($checkclass['opstate'] == '1') {
                echo "<script>parent.layer.alert('系统不允许重复操作！');self.location=document.referrer;</script>";
                exit;
            } else {
                if ($checkclass['opstate'] == '5') {
                    echo "<script>parent.layer.alert('系统不允许重复操作！');self.location=document.referrer;</script>";
                    exit;
                }
            }

            $checkusr = Db::table('user')
                ->where('username', $date['username'])
                ->where('u_name', $date['othername'])
                ->select();//用户名重复性检测
            if ($checkusr) {
                $time = date('Y-m-d H:i:s');
                $editscore = Db::table('scoreoperation')
                    ->where('id', $date['id'])
                    ->update([
                        'opstate' => $date['opstate'],
                        'othername' => $date['othername'],
                        'othertime' => $time,
                        'otherstate' => $date['opstate'],
                        'info' => $date['info']]);//修改操作
                if ($editscore) {
                    if ($date['opstate'] == '1') {
                        $stuScoreOperation=Db::name('scoreoperation')->where('id',$date['id'])->find();
                        $result=$this->ExaminScoreOper($stuScoreOperation['stuid'],$stuScoreOperation['score'],$stuScoreOperation['opscoreclass']);
                        if ($result){
                            echo "<script type='text/javascript'>parent.layer.alert('操作成功！');parent.history.go(-1);</script>";
                            exit();
                        }elseif(!$result){
                            echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                            exit;//判断更新操作是否成功
                        }
                    }if ($date['opstate'] == '5') {
                        $opres = Db::table('students')->where('s_id', $date['s_id'])->setInc('score', 0);
                        echo "<script>parent.layer.alert('操作成功！');self.location=document.referrer;;</script>";
                        exit;

                    }
                } else {
                    echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script>parent.layer.alert('参数错误！');self.location=document.referrer;;</script>";
                exit;
            }

        }
    }

//日志模块模块开始-------------------------------------------------------------------》

    public function scorelog()//学分操作日志页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看学分操作日志。',
            'state' => '异常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function scoreloglist()//学分操作日志列表后台
    {
        $usrname = session('username');
        $usrinfo = Db::table('user')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];
        $usrname = session('username');
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('collegeid', $usrcollege)
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('collegeid', $usrcollege)
            ->limit($start, $limit)
            ->order('datetime desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function scorelogload(Request $request)//学分操作日志列表重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->limit($start, $limit)
            ->order("id desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

    public function editscorelog()//学分日志查看页面
    {
        $date = input('get.');
        $validate = new validate([
            ['id', 'require|regex:int', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>alert('$msg');history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('score_view')
                ->where('id', $date['id'])
                ->find();//通过session查询个人信息

            $this->assign('data', $result);
            return $this->fetch();
        }
    }

    public function editscorelogrun()//学分操作日志编辑操作
    {
        $date = input('post.');
        $usrname = session('username');

        $validate = new validate([
            ['opstate', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|regex:int', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],

        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');self.location=document.referrer;</script>";
            exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                ->where('opstate', '4')
                ->where('id', $date['id'])
                ->select();
            if ($checkclass) {
                echo "<script>parent.layer.alert('该操作已被撤销，请勿重复提交相同操作！');self.location=document.referrer;</script>";
            } else {
                if ($date['opclass'] == '加分') {
                    $opres = Db::table('students')->where('s_id', $date['s_id'])->setDec('score', $date['score']);

                } else {
                    $opres = Db::table('students')->where('s_id', $date['s_id'])->setInc('score', $date['score']);

                }
                $time = date('Y-m-d H:i:s');
                $editscore = Db::table('scoreoperation')
                    ->where('id', $date['id'])
                    ->update([
                        'opstate' => '4',
                        'othername' => $date['othername'],
                        'othertime' => $time,
                        'otherstate' => '4',
                        'info' => $date['info']]);//修改操作
                if ($editscore) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '对学分操作流水号为：' . $date['id'] . ' 进行了操作。',
                        'state' => '异常',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script>parent.layer.alert('保存成功！');self.location=document.referrer;;</script>";
                    exit;
                } else {
                    echo "<script>parent.layer.alert('参数错误，请返回重试！');self.location=document.referrer;;</script>";
                    exit;//判断更新操作是否成功
                }

            }
        }

    }


}