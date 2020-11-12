<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\model\User as UserModel;
use think\validate;
use think\Request;
use think\Env;
use think\View;
use think\Loader;

//代码中具体分页代码及表格重载代码解释参照layui官方手册
class Apartment extends Controller//权限1
{
    protected function _initialize()
    {
        $usrname = session('username');
        if (empty($usrname)) {

            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "http://127.0.0.1:8088" }, 5000);</script><span class="STYLE12">&nbsp;:(</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp检测到系统环境异常！系统将在5秒后正在自动跳转。<br>&nbsp&nbsp&nbsp&nbsp&nbsp您的操作已被中止，这可能是非法登陆或登陆超时导致，您可尝试重新登陆系统。<br/></body></html>';
            exit;
        } else {
            $result = Db::table('user')
                ->where('username', $usrname)
                ->where('jurisdiction', '9')
                ->where('state', '1')
                ->select();//通过session查询个人信息
            if ($result == false) {
                session('username', null);
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "http://127.0.0.1:8088" }, 5000);</script><span class="STYLE12">&nbsp;:(</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp检测到账户异常！系统将在5秒后自动跳转<br>&nbsp&nbsp&nbsp&nbsp&nbsp您的操作已被中止，这可能是权限不足或您的账户信息已被管理员修改，您可尝试重新登陆系统。<br/></body></html>';
                exit;
            }
        }
    }

    public function hddy()//首页左边栏
    {

        $result = Db::table('system')
            ->where('id', '1')
            ->find();//通过session查询个人信息
        $result1 = 3;
        $num1 = Db::name('score_view')->where('opstate', '2')->count();

            $this->assign('data1', $num1);
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
        $this->success('退出成功', 'Admin/login');
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
            ['add', 'require|length:11|number', '手机号码不能为空|手机号码限制为11位|手机号码限制全部为数字'],
            ['u_mail', 'email', '邮箱格式不正确'],
            ['qq', 'number|min:5|max:11', 'QQ号码限制全部为数字|QQ号码限制5-11位|QQ号码限制5-11位'],
            ['vx', 'min:5|max:20|alphaDash', '微信号码至少5位|微信号码限制不能超过20位|微信号码包含非法字符'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改个人信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
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
                    echo "<script type='text/javascript'>parent.layer.alert('修改成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
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
            ['password', 'require|min:5|max:20|alphaDash', '密码不能为空!|密码至少5位|密码不能超过20位|密码不能包含非法字符'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');parent.history.go(-1)</script>";
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
                        echo "<script>parent.layer.alert('原密码验证失败，请返回重试！');parent.history.go(-1);</script>";
                        exit;
                    }
                }
            } else {
                echo "<script>parent.layer.alert('参数错误，请返回重试！');parent.layerhistory.go(-1);</script>";
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
            ['password', 'min:5|max:20|alphaDash|require', '密码至少5位|密码不能超过20位|密码不能包含非法字符|密码不能为空'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改个人密码时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
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
                        session('username', null);
                        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "http://127.0.0.1:8088" }, 3000);</script><span class="STYLE12">&nbsp;:)</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp密码修改成功！系统正在自动跳转至登陆页面。<br/></body></html>';
                        exit;

                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('修改失败，请返回重试！');parent.history.go(-1);</script>";
                    }

                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('密码不一致，请返回重试！');parent.history.go(-1);</script>";
                }

            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
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
        $count = Db::name("zlog_view")
            ->where('username', $usrname)
            ->count("id");
        $cate_list = Db::name("zlog_view")
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
        $count = Db::name("zlog_view")
            ->where('username', $usrname)
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->count("id");
        $cate_list = Db::name("zlog_view")
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
            ['id', 'require|number', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '疑似在查看个人操作日志详情时篡改页面信息。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('zlog_view')
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
            ['id', 'require|number', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '疑似在查看个人操作日志详情(编辑)时篡改页面信息。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('zlog_view')
                ->where('id', $date['id'])
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
            ['opstate', 'require|number', '请选择操作类型！|参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|number', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],

        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                ->where('opstate', '4')
                ->where('id', $date['id'])
                ->select();//用户名重复性检测
            if ($checkclass) {
                echo "<script type='text/javascript'>parent.layer.alert('该操作已被撤销，请勿重复提交相同操作！');parent.history.go(-1)</script>";
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
                        echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.parent.parent.history.go(-1);</script>";
                        exit;
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                        exit;//判断更新操作是否成功
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('参数错误！');parenthistory.go(-1);</script>";
                    exit;
                }
            }
        }
    }

//用户管理模块开始部分-------------------------------------------------------------》

    public function adduser()//添加用户页面
    {
        $result1 = Db::name("userclass")
            ->where('userid',2)
            ->select();
        $result2 = Db::table('college')
            ->order('collegeid desc')
            ->select();
        $this->assign('data1', $result1);
        $this->assign('data2', $result2);
        return $this->fetch();
    }

    public function userclassmore()//添加用户部门类别联动
    {
        $usrcollege = input('get.');
        $usrdata = Db::name("college")
            ->where("class", $usrcollege['q'])
            ->where('collegeid',44)
            ->select();
        echo "<option value=''>未选择</option>";
        foreach ($usrdata as $value) {
            echo "<option value='{$value['collegeid']}'>{$value['collegeinfo']}</option></select>";
        }
    }

    public function adduserrun()//添加用户操作
    {
        $data = input('post.');
        $pwd = [
            'password' => '123456'
        ];
        $date = $data + $pwd;
        $validate = new validate([
            ['username', 'require|alphaDash|max:10', '用户名不能为空|用户名包含非法字符！|用户名过长！'],
            ['u_name', 'require|max:15|chs', '姓名不能为空|姓名长度过长！|姓名要求全部为汉字！'],
            ['u_sex', 'max:3|chs', '性别参数异常！|性别参数异常！'],
            ['user_id', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空|身份证号码限制为18位数字最后一位可为X！|身份证号码限制不能超过18位'],
            ['u_class', 'require|number', '所属单位不能为空|参数异常'],
            ['u_classinfo', 'require|number', '所属单位名称不能为空|参数异常'],
            ['add', 'length:11|number', '手机号码限制11位全数字！|手机号码限制11位全数字！'],
            ['u_mail', 'email|max:25', '邮箱格式不正确|邮箱输入过长！'],
            ['qq', 'min:5|max:11|number', 'QQ号码限制5-11位全部为数字!|QQ号码限制5-11位全部为数字!|QQ号码限制5-11位全部为数字!'],
            ['vx', 'max:25|alphaDash', '微信号码限制不能超过25位|微信号包含非法字符！'],
            ['state', 'max:5|number', '账号状态选项参数异常！|账号状态选项参数异常！'],
            ['jurisdiction', 'require|number', '权限未分配|账号权限选项参数异常！'],
            ['password', 'require|number|max:6', '用户密码初始化失败！|用户密码初始化失败！|用户密码初始化失败！'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加用户时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('user')
                ->where('username', $date['username'])
                ->select();//用户名重复性检测
            if ($result) {
                echo "<script type='text/javascript'>parent.layer.alert('用户名已存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                if ($date['password'] == '123456') {
                    $user = new UserModel($date);
                    $ret = $user->allowField(true)->save();
                    if ($ret) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '添加了用户名为：' . $date['username'] . ' 的用户，添加用户。',
                            'state' => '正常',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        $this->success("用户 {$date['username']} 添加成功！");
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('用户添加失败！');parent.history.go(-1);</script>";
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('用户密码初始化失败！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function showuser()//显示用户页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有用户。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function userlist()//用户列表后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("user_view")
            ->count("u_id");
        $cate_list = Db::name("user_view")
            ->limit($start, $limit)
            ->order('u_id desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function usercheck(Request $request)//用户查询表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("user_view")
            ->where('username|u_name|collegeinfo', 'like', "%" . $date["username"] . "%")
            ->count("u_id");
        $cate_list = Db::name("user_view")
            ->where('username|u_name|collegeinfo', 'like', "%" . $date["username"] . "%")
            ->limit($start, $limit)
            ->order("username desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

    public function deluser()//删除用户
    {
        $data = input('post.');
        $res = Db('user')->where('u_id', $data['u_id'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));

    }

    public function editshow()//编辑用户页面
    {
        $date = input('get.');
        $validate = new validate([
            ['id', 'require|number', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result1 = Db::table('user_view')
                ->where('u_id', $date['id'])
                ->find();//通过session查询个人信息
            $result2 = Db::table('college')
                ->select();//通过session查询个人信息
            $this->assign('data', $result1);
            $this->assign('data2', $result2);
            return $this->fetch();
        }
    }

    public function resuserpwdrun()//恢复密码操作
    {
        $date = input('post.');
        $newpd = '123456';
        $result = Db::table('user')
            ->where('username', $date['username'])
            ->update([
                'password' => md5($newpd)]);
        if ($result) {

            $status = '1';
        } else {
            $status = '0';
        }


        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '重置用户名为：' . $date['username'] . ' 的密码。',
            'state' => '重要',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);


        exit(json_encode($status));
    }

    public function editshowrun()//编辑用户操作
    {
        $date = input('post.');
        $validate = new validate([
            ['u_id', 'require|alphaDash|max:10', '未知参数异常，请返回重试！|未知参数异常，请返回重试！|未知参数异常，请返回重试！'],
            ['username', 'require|alphaDash|max:10', '用户名参数异常，请返回重试！|用户名参数异常，请返回重试！|用户名参数异常，请返回重试！'],
            ['u_name', 'require|max:15|chs', '姓名不能为空|姓名长度过长！|姓名要求全部为汉字！'],
            ['u_sex', 'max:3|chs', '性别参数异常！|性别参数异常！'],
            ['user_id', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空|身份证号码限制为18位数字最后一位可为X！|身份证号码限制不能超过18位'],
            ['u_class', 'require|number', '所属单位不能为空|参数异常'],
            ['u_classinfo', 'require|number', '所属单位名称不能为空|参数异常'],
            ['add', 'length:11|number', '手机号码限制11位全数字！|手机号码限制11位全数字！'],
            ['u_mail', 'email|max:25', '邮箱格式不正确|邮箱输入过长！'],
            ['qq', 'min:5|max:11|number', 'QQ号码限制5-11位全部为数字!|QQ号码限制5-11位全部为数字!|QQ号码限制5-11位全部为数字!'],
            ['vx', 'max:25|alphaDash', '微信号码限制不能超过25位|微信号包含非法字符！'],
            ['state', 'max:5|number', '账号状态选项参数异常！|账号状态选项参数异常！'],
            ['jurisdiction', 'require|number', '权限参数异常！|权限参数异常！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $userinfocheck = Db::table('user')
                ->where('u_id', $date['u_id'])
                ->where('username', $date['username'])
                ->select();
            if ($userinfocheck) {
                Db::table('user')
                    ->where('u_id', $date['u_id'])
                    ->update([
                        'u_name' => $date['u_name'],
                        'u_sex' => $date['u_sex'],
                        'user_id' => $date['user_id'],
                        'u_class' => $date['u_class'],
                        'u_classinfo' => $date['u_classinfo'],
                        'add' => $date['add'],
                        'u_mail' => $date['u_mail'],
                        'qq' => $date['qq'],
                        'state' => $date['state'],
                        'jurisdiction' => $date['jurisdiction'],
                        'vx' => $date['vx']]);//修改操作
                if ($this) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '修改用户名为：' . $date['username'] . ' 的信息。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    $this->success("{$date['username']} 的信息修改成功！");
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
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
        $result = Db::table('class')
            ->order("class desc")
            ->select();
        $result2 = Db::table('apartment')
            ->order('apartmentid desc')
            ->select();
        $this->assign('data', $result);
        $this->assign('data1', $result2);
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

//       utf8中文字符一个汉字占3个字节
        $validate = new validate([
            ['s_id', 'require|number|min:10|max:15', '学号不能为空！|学号限制全部数字！|学号至少10位！|学号输入过长！'],
            ['s_name', 'require|chs|max:15', '姓名不能为空！|姓名只能为5位以内的汉字！'],
            ['s_sex', 'require|chs|max:3', '性别不能为空！|性别参数异常，请返回重试！|性别参数异常，请返回重试！'],
            ['s_proid', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空！|身份证号码限制18位数字，最后一位可以为X！|身份证号码限制不能超过18位！'],
            ['s_add', 'length:11|number', '学生手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_home', 'max:60', '家庭住址限制20个字符以内'],
            ['s_class', 'require|number|max:10', '未选择班级！|班级参数异常，请返回重试！|班级参数异常，请返回重试！'],
            ['s_room', 'require|max:10|alphaDash', '寝室信息不能为空！|寝室信息输入过长！|寝室信息包含非法字符！'],
            ['s_apartment', 'require|number', '未选择公寓号|参数异常，请返回重试'],
            ['s_dormitory', 'require|number', '未选择寝室|参数异常，请返回重试'],
            ['s_dadname', 'max:15|chs', '父亲姓名至多输入5个汉字|父亲姓名限制为全汉字'],
            ['s_dadadd', 'length:11|number', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_mumname', 'max:15|chs', '母亲姓名至多输入5个汉字|母亲姓名限制为全汉字'],
            ['s_mumadd', 'length:11|number', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学生时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        }
        else {
            $date['apartment'] = Db::table('apartment')->where('apartmentid', $date['s_apartment'])->value('apartmentinfo');
            $date['dormitory'] = Db::table('dormitory')->where('dormitoryid', $date['s_dormitory'])->value('dormitoryinfo');
            $result = Db::table('students')
                ->where('s_id', $date['s_id'])
                ->whereOr('s_proid', $date['s_proid'])
                ->select();//用户名重复性检测
            if ($result) {
                echo "<script type='text/javascript'>parent.layer.alert('该学生信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                Db::table('students')->insert($date);
                if ($this) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '添加了学号为：' . $date['s_id'] . ' 的学生信息。',
                        'state' => '正常',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('学生信息添加成功！');parent.history.go(-1);</script>";
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('学生信息添加失败！');parent.history.go(-1);</script>";
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
            //$data['s_apartment'] = Db::table('apartment')->where('apartmentinfo', $excel->getActiveSheet()->getCell("H" . $i)->getValue())->value('apartmentid');
            //$data['s_dormitory'] = Db::table('dormitory')->where('dormitoryinfo', $excel->getMacrosCertificate()->getCell("I" . $i)->getValue())->value('dormitoryid');
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
//            Db::table("students")->insert($data);
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
                //$data['s_apartment'] = Db::table('apartment')->where('apartmentinfo', $excel->getActiveSheet()->getCell("H" . $i)->getValue())->value('apartmentid');
                //$data['s_dormitory'] = Db::table('dormitory')->where('dormitoryinfo', $excel->getMacrosCertificate()->getCell("I" . $i)->getValue())->value('dormitoryid');
                echo "<td> " . $data['s_id'] . " " . $data['s_name'] . " " . $data['s_proid'] . " "
                    . $data['s_sex'] . " " . $data['s_class'] . " " . $data['s_room'] . " "
                    . $data['s_add'] ." " . $data['apartment'] . " " . $data['dormitory'] ."</td>";
            }
            echo "</tr>";
        } else {
            echo "<script type='text/javascript'>parent.layer.alert('数据导入失败，请返回重试！');parent.history.go(-1);</script>";
        }
    }

    public function showstu()//学生查询页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有学生。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function stulist()//学生查询列表后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("stu_view")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            ->limit($start, $limit)
            ->order('s_id desc')->select();
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
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询

        $count = Db::name("stu_view")
            ->where('s_name|s_id|class|teacherinfo|apartmentinfo|dormitoryinfo', 'like', "%" . $date["s_name"] . "%")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
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
            ['s_id', 'require|number|min:10|max:15', '学号不能为空！|学号限制全部数字！|学号至少10位！|学号输入过长！'],
            ['s_name', 'require|chs|max:15', '姓名不能为空！|姓名只能为5位以内的汉字！|姓名只能为5位以内的汉字！'],
            ['s_sex', 'require|chs', '性别不能为空！|性别参数异常！'],
            ['s_proid', 'require|[0-9]{17}[0-9xX]|max:18', '身份证号码不能为空！|身份证号码限制18位数字，最后一位可以为X！|身份证号码限制不能超过18位！'],
            ['s_add', 'length:11|number', '学生手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_home', 'max:60', '家庭住址限制20个字符以内'],
            ['s_class', 'require|number|max:10', '未选择班级！|班级参数异常，请返回重试！|班级参数异常，请返回重试！'],
            ['s_room', 'require|max:10|alphaDash', '寝室信息不能为空！|寝室信息输入过长！|寝室信息包含非法字符！'],
            ['s_dadname', 'max:15|chs', '父亲姓名至多输入5个汉字|父亲姓名限制为全汉字'],
            ['s_dadadd', 'length:11|number', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
            ['s_mumname', 'max:15|chs', '母亲姓名至多输入5个汉字|母亲姓名限制为全汉字'],
            ['s_mumadd', 'length:11|number', '手机号码限制为11位全数字|手机号码限制为11位全数字'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改学号为：' . $date['s_id'] . ' 的信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent,history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
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
                echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                exit;
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('保存参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;//判断更新操作是否成功
            }
        }
    }

//系统参数设置开始部分-------------------------------------------------------------》

    public function teacheradmin()//辅导员管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有辅导员信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function delteacher()//删除班级
    {
        $data = input('post.');
        $res = Db('teacher')->where('teacherid', $data['teacherid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function teacherlist()//辅导员信息列表后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("teacher_view")
            ->count("teacherid");
        $cate_list = Db::name("teacher_view")
            ->limit($start, $limit)
            ->order('teacherid desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;


        return json($list);
    }

    public function teachercheck(Request $request)//辅导员信息表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("teacher_view")
            ->where('teacherinfo|collegeinfo', 'like', "%" . $date["techerinfo"] . "%")
            ->count("teacherid");
        $cate_list = Db::name("teacher_view")
            ->where('teacherinfo|collegeinfo', 'like', "%" . $date["techerinfo"] . "%")
            ->limit($start, $limit)
            ->order("teacherid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($list);//返回数据给前端
    }

    public function showteacher()//查看辅导员信息页面
    {
        $date = input('get.');
        $result1 = Db::name("teacher_view")
            ->where('teacherid', $date["id"])
            ->find();
        $result2 = Db::table('college')
            ->where('class', '1')
            ->select();

        $this->assign('data', $result1);
        $this->assign('data1', $result2);
        return $this->fetch();
    }

    public function editteacher()//辅导员信息编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['teacherid', 'require|number', '辅导员信息参数异常，请返回重试！|辅导员信息参数异常，请返回重试！'],
            ['teacherinfo', 'chs|require|max:15', '姓名必须为汉字|姓名不能为空|姓名不能超过5位！'],
            ['teacheradd', 'require|length:11|number', '手机号码不能为空|手机号码限制为11位|手机号码限制全部为数字'],
            ['collegeid', 'require|number', '所属学院不能为空！|所属学院信息参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改辅导员：' . $date['teacherid'] . ' 的信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            Db::table('teacher')
                ->where('teacherid', $date['teacherid'])
                ->update([
                    'teacherinfo' => $date['teacherinfo'],
                    'teacheradd' => $date['teacheradd'],
                    'teachersex' => $date['teachersex'],
                    'collegeid' => $date['collegeid']
                ]);//修改操作
            if ($this) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '修改辅导员姓名为：' . $date['teacherinfo'] . '的信息。',
                    'state' => '重要',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                exit;
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('保存失败，请稍后再试！');parent.history.go(-1);</script>";
                exit;//判断更新操作是否成功
            }
        }
    }

    public function addteacher()//添加辅导员页面
    {
        $result = Db::table('college')
            ->where('class', '1')
            ->select();

        //$rs1=json($result);
        $this->assign('data', $result);

        return $this->fetch();
    }

    public function addteacherrun()//添加辅导员操作
    {
        $date = input('post.');
        $validate = new validate([
            ['teacherinfo', 'chs|require|max:15', '姓名必须为汉字|姓名不能为空|姓名不能超过5位！'],
            ['teacheradd', 'require|length:11|number', '手机号码不能为空|手机号码限制为11位|手机号码限制全部为数字'],
            ['teachersex', 'require|chs|max:5', '辅导员性别参数异常，请返回重试！|辅导员性别参数异常，请返回重试！|辅导员性别参数异常，请返回重试！'],
            ['collegeid', 'require|number', '所属学院不能为空|所属学院参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加辅导员信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('teacher')
                ->where('teacherinfo', $date['teacherinfo'])
                ->select();//用户名重复性检测
            if ($result) {
                echo "<script type='text/javascript'>parent.layer.alert('该辅导员信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                Db::table('teacher')->insert($date);
                if ($this) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '添加了辅导员姓名为：' . $date['teacherinfo'] . ' 的信息。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('辅导员信息添加成功！');parent.history.go(-1);</script>";
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('辅导员信息添加失败！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function collegeadmin()//学院管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有学院信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function delcollege()//删除学院信息
    {
        $data = input('post.');
        $res = Db('college')->where('collegeid', $data['collegeid'])->delete();
        $res1 = Db('major')->where('collegeid', $data['collegeid'])->delete();
        if ($res && $res1) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function collegelist()//学院信息表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("college")
            ->where('class', '1')
            ->count("collegeid");
        $cate_list = Db::name("college")
            ->where('class', '1')
            ->limit($start, $limit)
            ->order('collegeid desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function collegecheck(Request $request)//学院信息表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("college")
            ->where('collegeinfo', 'like', "%" . $date["collegeinfo"] . "%")
            ->where('class', '1')
            ->count("collegeid");
        $cate_list = Db::name("college")
            ->where('collegeinfo', 'like', "%" . $date["collegeinfo"] . "%")
            ->where('class', '1')
            ->limit($start, $limit)
            ->order("collegeid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

    public function addcollege()//添加学院页面
    {
        return $this->fetch();
    }

    public function addcollegerun()//添加学院操作
    {
        $data = input('post.');
        $dataclass1 = [
            'class' => '1',
        ];
        $date = $data + $dataclass1;
        $validate = new validate([
            ['collegeinfo', 'require|max:45|chs', '学院名称不能为空！|学院名称限制为15位以内全汉字！|学院名称限制为15位以内全汉字！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学院信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('college')
                ->where('collegeinfo', $date['collegeinfo'])
                ->where('class', '1')
                ->select();//用户名重复性检测
            if ($result) {
                echo "<script type='text/javascript'>parent.layer.alert('该学院信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                if ($date['class'] == '1') {
                    Db::table('college')->insert($date);
                    if ($this) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '添加了学院名为：' . $date['collegeinfo'] . ' 的信息。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        echo "<script type='text/javascript'>parent.layer.alert('学院信息添加成功！');parent.history.go(-1);</script>";
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('学院信息添加失败！');parent.history.go(-1);</script>";
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('学院信息参数错误，请返回重试！');parent.history.go(-1);</script>";
                }

            }
        }
    }

    public function showcollege()//查看学院页面
    {
        $date = input('get.');
        $result1 = Db::name("college")
            ->where('collegeid', $date["id"])
            ->find();
        $result2 = Db::table('teacher_view')
            ->where('collegeid', $date["id"])
            ->select();
        $result3 = Db::table('major')
            ->where('collegeid', $date["id"])
            ->select();
        $result4 = Db::table('class_view')
            ->where('collegeid', $date["id"])
            ->select();

        $this->assign('data', $result1);
        $this->assign('data1', $result2);
        $this->assign('data2', $result3);
        $this->assign('data3', $result4);
        return $this->fetch();
    }

    public function editcollege()//学院编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['collegeid', 'require|number', '学院信息参数异常，请返回重试！|学院信息参数异常，请返回重试！'],
            ['collegeinfo', 'require|chs|max:45', '学院名称不能为空！|学院名称限制为15以内全汉字！|学院名称限制为15以内全汉字！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑' . $date[collegeid] . '异常。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $collegecheck = Db::table('college')
                ->where('collegeid', $date['collegeid'])
                ->where('class', '1')
                ->select();
            if ($collegecheck) {
                $collegeedit = Db::table('college')
                    ->where('collegeid', $date['collegeid'])
                    ->update([
                        'collegeinfo' => $date['collegeinfo']
                    ]);//修改操作
                if ($collegeedit) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '修改学院名称为：' . $date['collegeinfo'] . ' 的信息 。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('学院信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('学院信息参数错误，请返回重试！');parent.history.go(-1);</script>";
            }
        }
    }

    public function majoradmin()//专业管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有专业。',
            'state' => '重要',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function delmajor()//删除专业信息
    {
        $data = input('post.');
        $res = Db('major')->where('majorid', $data['majorid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function majorlist()//专业表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("major_view")
            ->count("majorid");
        $cate_list = Db::name("major_view")
            ->limit($start, $limit)
            ->order('majorid desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function majorcheck(Request $request)//专业信息表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("major_view")
            ->where('majorinfo|collegeinfo', 'like', "%" . $date["majorinfo"] . "%")
            ->count("majorid");
        $cate_list = Db::name("major_view")
            ->where('majorinfo|collegeinfo', 'like', "%" . $date["majorinfo"] . "%")
            ->limit($start, $limit)
            ->order("majorid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($list);//返回数据给前端
    }

    public function addmajor()//专业添加页面
    {

        $result1 = Db::table('college')
            ->where('class', '1')
            ->select();
        $this->assign('data', $result1);

        return $this->fetch();
    }

    public function addmajorrun()//专业添加操作
    {
        $date = input('post.');
        $validate = new validate([
            ['majorinfo', 'require|max:45|chs', '专业名称不能为空！|专业名称限制为15位以内且全部为汉字|专业名称限制为15位以内且全部为汉字'],
            ['collegeid', 'require|number', '所属学院不能为空！|所属学院参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加专业信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $majorcheck = Db::table('major')
                ->where('majorinfo', $date['majorinfo'])
                ->select();//用户名重复性检测
            if ($majorcheck) {
                echo "<script type='text/javascript'>parent.layer.alert('该专业信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                $majoradd = Db::table('major')->insert($date);
                if ($majoradd) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '添加了专业名称为：' . $date['majorinfo'] . ' 的信息。',
                        'state' => '正常',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('专业信息添加成功！');parent.history.go(-1);</script>";
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('专业信息添加失败！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function showmajor()//专业信息查看页面
    {
        $date = input('get.');
        $result1 = Db::name("major_view")
            ->where('majorid', $date["id"])
            ->find();
        $result2 = Db::table('college')
            ->where('class', '1')
            ->select();

        $this->assign('data', $result1);
        $this->assign('data1', $result2);
        return $this->fetch();
    }

    public function editmajor()//专业信息编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['majorid', 'require|number', '专业信息参数异常，请返回重试！|专业信息参数异常，请返回重试！'],
            ['majorinfo', 'require|max:45|chs', '专业名称不能为空！|专业名称限制为15位以内且全部为汉字|专业名称限制为15位以内且全部为汉字'],
            ['collegeid', 'require|number', '所属学院不能为空！|所属学院参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '修改专业信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

            $majorcheck = Db::table('major')
                ->where('majorid', $date['majorid'])
                ->select();
            if ($majorcheck) {
                $editmajor = Db::table('major')
                    ->where('majorid', $date['majorid'])
                    ->update([
                        'majorinfo' => $date['majorinfo'],
                        'collegeid' => $date['collegeid']
                    ]);//修改操作
                if ($editmajor) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '修改了专业名为：' . $date['majorinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('专业信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('专业信息参数错误，请返回重试！');parent.history.go(-1);</script>";
            }
        }
    }

    public function classadmin()//班级信息管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有班级信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function delclass()//删除班级
    {
        $data = input('post.');
        $res = Db('class')->where('class', $data['class'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function classlist()//班级信息表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("class_view")
            ->count("class");
        $cate_list = Db::name("class_view")
            ->limit($start, $limit)
            ->order('class desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }
        return json($list);
    }

    public function classcheck(Request $request)//班级信息表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("class_view")
            ->where('class|collegeinfo|majorinfo|teacherinfo', 'like', "%" . $date["classinfo"] . "%")
            ->count("class");
        $cate_list = Db::name("class_view")
            ->where('class|collegeinfo|majorinfo|teacherinfo', 'like', "%" . $date["classinfo"] . "%")
            ->limit($start, $limit)
            ->order("class desc")
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

    public function addclass()//添加班级页面
    {
        $result1 = Db::name("teacher_view")
            ->select();
        $result2 = Db::name("major")
            ->select();
        $result3 = Db::table('college')
            ->where('class', '1')
            ->select();
        $this->assign('data1', $result1);
        $this->assign('data2', $result2);
        $this->assign('data3', $result3);
        return $this->fetch();
    }

    public function addclassrun()//添加班级信息操作
    {
        $date = input('post.');
        $validate = new validate([
            ['class', 'require|number|min:7|max:10', '班级不能为空！|班级号限制为7-10位全数字！|班级号限制为7-10位全数字！|班级号限制为7-10位全数字！'],
            ['teacherid', 'require|number', '辅导员不能为空！|辅导员信息参数异常，请返回重试！'],
            ['majorid', 'require|number', '所属专业不能为空！|所属专业参数异常，请返回重试！'],
            ['collegeid', 'require|number', '所在学院不能为空！|所在学院参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加班级信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $classcheck = Db::table('class')
                ->where('class', $date['class'])
                ->select();//用户名重复性检测
            if ($classcheck) {
                echo "<script type='text/javascript'>parent.layer.alert('该班级信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                $classadd = Db::table('class')->insert($date);
//              $classadd1=Db::view('classview')->insert($date);
                if ($classadd) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '添加了班级为：' . $date['class'] . ' 的信息。',
                        'state' => '正常',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('班级信息添加成功！');parent.history.go(-1);</script>";
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('班级信息添加失败！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function showclass()//班级信息查看页面
    {
        $date = input('get.');
        $result1 = Db::name("class_view")
            ->where('class', $date["id"])
            ->find();//使用find前端可以直接输出
        $result2 = Db::table('teacher_view')
            ->select();//回传导员信息
        $result3 = Db::table('major_view')
            ->order("collegeid desc")
            ->select();//回传专业信息
        $result4 = Db::table('college')
            ->where('class', '1')
            ->order("collegeid desc")
            ->select();//回传学院信息

        $this->assign('data', $result1);
        $this->assign('data1', $result2);
        $this->assign('data2', $result3);
        $this->assign('data3', $result4);
        return $this->fetch();
    }

    public function editclass()//班级信息编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['class', 'require|number|min:7|max:10', '班级参数异常，请稍后再试！|班级限制为7-10位全数字！|班级限制为7-10位全数字！|班级限制为7-10位全数字！'],
            ['teacherid', 'require|number', '辅导员信息不能为空！|辅导员信息参数异常，请返回重试！'],
            ['majorid', 'require|number', '所属专业不能为空！|所属专业参数异常，请返回重试！'],
            ['collegeid', 'require|number', '所在学院不能为空！|所在学院参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑班级：' . $date['class'] . ' 的信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $classcheck = Db::table('class')
                ->where('class', $date['class'])
                ->select();//更新数据库之前检测班级是否修改
            if ($classcheck) {
                $majorcollegecheck = Db::table('major_view')
                    ->where('majorid', $date['majorid'])
                    ->where('collegeid', $date['collegeid'])
                    ->select();//判断专业和学院是否相符
                if ($majorcollegecheck) {
                    $class = Db::table('class')
                        ->where('class', $date['class'])
                        ->update([
                            'teacherid' => $date['teacherid'],
                            'majorid' => $date['majorid'],
                            'collegeid' => $date['collegeid']
                        ]);//修改操作
                    if ($class) {
                        $syslog = ['ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '修改了班级为：' . $date['class'] . ' 的信息。',
                            'state' => '重要',
                            'username' => $usrlogo = session('username'),];
                        Db::table('systemlog')->insert($syslog);
                        echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                        exit;
                    } else {
                        echo "<script type='text/javascript'>parent.layer.alert('班级信息未修改！');parent.history.go(-1);</script>";
                        exit;//判断更新操作是否成功
                    }
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('专业与学院不符！');parent.history.go(-1);</script>";
                    exit;
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('班级参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
            }
        }
    }

    public function scoreadmin()//学分操作管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有学分操作信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function delfirscore()//删除学分操作一级分类
    {
        $data = input('post.');
        $res = Db('scorefirst')->where('scoreid', $data['scoreid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function delsecscore()//删除学分操作二级分类
    {
        $data = input('post.');
        $res = Db('scoresec')->where('scoresecid', $data['scoresecid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function scorefirlist()//一级分类表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("scorefir_view")
            ->count("scoreid");
        $cate_list = Db::name("scorefir_view")
            ->limit($start, $limit)
            ->order("scoreid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }
        return json($list);
    }

    public function scorefircheck(Request $request)//一级分类表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("scorefir_view")
            ->where('scoreinfo|collegeinfo', 'like', "%" . $date["scoreinfo"] . "%")
            ->count("collegeid");
        $cate_list = Db::name("scorefir_view")
            ->where('scoreinfo|collegeinfo', 'like', "%" . $date["scoreinfo"] . "%")
            ->limit($start, $limit)
            ->order("scoreid desc")
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

    public function scoreseclist()//二级分类表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("scoresec_view")
            ->count("scoresecid");
        $cate_list = Db::name("scoresec_view")
            ->limit($start, $limit)
            ->order("scoresecid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }
        return json($list);
    }

    public function scoreseccheck(Request $request)//二级分类表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("scoresec_view")
            ->where('scoresecinfo|scoreinfo', 'like', "%" . $date["scoresecinfo"] . "%")
            ->count("scoresecid");
        $cate_list = Db::name("scoresec_view")
            ->where('scoresecinfo|scoreinfo', 'like', "%" . $date["scoresecinfo"] . "%")
            ->limit($start, $limit)
            ->order("scoresecid desc")
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

    public function addscorefir()//添加一级分类页面
    {
        $result = Db::name("college")
            ->order('collegeid desc')
            ->select();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function addscorefirrun()//添加一级分类操作
    {
        $date = input('post.');
        $validate = new validate([
            ['collegeid', 'require|number', '所属单位名称不能为空|参数错误，请返回重试！'],
            ['scoreinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学分操作一级分类时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checkfirin = Db::table('scorefirst')
                ->where('scoreinfo', $date['scoreinfo'])
                ->select();//判断专业和学院是否相符
            if ($checkfirin) {
                $this->error("似乎已经在系统中已经存在这条分类:{$date['scoreinfo']}");
            }
            $scorefirrun = Db::table('scorefirst')->insert($date);
            if ($scorefirrun) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '添加了学分操作一级分类为：' . $date['scoreinfo'] . ' 的信息。',
                    'state' => '正常',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script type='text/javascript'>parent.layer.alert('学分操作一级分类信息添加成功！');parent.history.go(-1);</script>";
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('学分操作一级分类添加失败！');parent.history.go(-1);</script>";
            }
        }

    }

    public function addscoresec()//添加二级分类页面
    {
        $result = Db::name("scorefirst")
            ->order('scoreid desc')
            ->select();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function addscoresecrun()//添加二级分类操作
    {
        $date = input('post.');
        $validate = new validate([
            ['scorefirid', 'require|number', '所属分类名称不能为空|所属分类参数错误，请返回重试！'],
            ['classid', 'require|number', '操作类型不能为空|操作类型参数错误，请返回重试！'],
            ['score', 'require|number', '分数上限不能为空！|分数上限参数异常，请返回重试！'],
            ['scoresecinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学分操作二级分类时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checksecin = Db::table('scoresec')
                ->where('scoresecinfo', $date['scoresecinfo'])
                ->where('classid', $date['classid'])
                ->select();//判断专业和学院是否相符
            if ($checksecin) {
                $this->error("似乎已经在系统中已经存在这条分类:{$date['scoresecinfo']}");
            }
            $scoresecrun = Db::table('scoresec')->insert($date);
            if ($scoresecrun) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '添加了学分操作二级分类为：' . $date['scoresecinfo'] . ' 的信息。',
                    'state' => '正常',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script type='text/javascript'>parent.layer.alert('学分操作二级分类信息添加成功！');parent.history.go(-1);</script>";
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('学分操作二级分类添加失败！');parent.history.go(-1);</script>";
            }
        }
    }

    public function showscorefir()//查看一级分类页面
    {
        $date = input('get.');
        $result1 = Db::name("scorefir_view")
            ->where('scoreid', $date["id"])
            ->find();//使用find前端可以直接输出
        $result2 = Db::table('college')
            ->select();//判断
        $this->assign('data', $result1);
        $this->assign('data2', $result2);
        return $this->fetch();
    }

    public function editscorefir()//编辑一级分类操作
    {
        $date = input('post.');
        $validate = new validate([
            ['collegeid', 'require|number', '所属单位参数异常，请返回重试！|所属单位参数异常，请返回重试！'],
            ['scoreinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑学分操作一级分类信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

            $scorefircheck = Db::table('scorefir_view')
                ->where('scoreid', $date['scoreid'])
                ->select();//判断
            if ($scorefircheck) {
                $scorefir = Db::table('scorefirst')
                    ->where('scoreid', $date['scoreid'])
                    ->update([
                        'collegeid' => $date['collegeid'],
                        'scoreinfo' => $date['scoreinfo']
                    ]);//修改操作
                if ($scorefir) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '编辑学分操作一级分类为' . $date['scoreinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('一级分类信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
            }

        }

    }

    public function showscoresec()//查看二级分类页面
    {
        $date = input('get.');
        $result1 = Db::name("scoresec_view")
            ->where('scoresecid', $date["id"])
            ->find();//使用find前端可以直接输出
        $result2 = Db::table('scorefirst')
            ->select();//判断
        $this->assign('data', $result1);
        $this->assign('data2', $result2);
        return $this->fetch();
    }

    public function editscoresec()//编辑二级分类操作
    {
        $date = input('post.');
        $validate = new validate([
            ['scoresecid', 'require|number', '操作参数异常，请返回重试！|操作参数异常，请返回重试！'],
            ['scorefirid', 'require|number', '所属一级分类参数异常，请返回重试！|所属一级分类参数异常，请返回重试！'],
            ['classid', 'require|number', '操作类型参数异常，请返回重试！|操作类型参数异常，请返回重试！'],
            ['score', 'require|number', '分数参数异常，请返回重试！|分数参数异常，请返回重试！'],
            ['scoresecinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑学分操作二级分类时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

            $scoreseccheck = Db::table('scoresec_view')
                ->where('scoresecid', $date['scoresecid'])
                ->select();//判断
            if ($scoreseccheck) {
                $scoresec = Db::table('scoresec')
                    ->where('scoresecid', $date['scoresecid'])
                    ->update([
                        'scorefirid' => $date['scorefirid'],
                        'classid' => $date['classid'],
                        'score' => $date['score'],
                        'scoresecinfo' => $date['scoresecinfo'],
                    ]);//修改操作
                if ($scoresec) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '编辑了学分操作二级分类为' . $date['scoresecinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('二级分类信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
            }

        }
    }

    public function apartmentadmin()//公寓信息管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有公寓信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function addapartment()//添加公寓楼号页面
    {
        $result = Db::name("college")
            ->order('collegeid desc')
            ->select();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function addapartmentidrun()//添加公寓楼号操作
    {
        $date = input('post.');
        $validate = new validate([
            ['collegeid', 'require|number', '所属单位名称不能为空|参数错误，请返回重试！'],
            ['apartmentinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加公寓楼号时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checkfirin = Db::table('apartment')
                ->where('apartmentinfo', $date['apartmentinfo'])
                ->select();//判断专业和学院是否相符
            if ($checkfirin) {
                $this->error("似乎已经在系统中已经存在这个公寓楼号:{$date['apartmentinfo']}");
            }
            $scorefirrun = Db::table('apartment')->insert($date);
            if ($scorefirrun) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '添加了公寓楼号为：' . $date['apartmentinfo'] . ' 的信息。',
                    'state' => '正常',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script type='text/javascript'>parent.layer.alert('公寓楼号添加成功！');parent.history.go(-1);</script>";
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('公寓楼号添加失败！');parent.history.go(-1);</script>";
            }
        }
    }

    public function adddormitory()//添加学生寝室页面
    {
        $result = Db::name("apartment")
            ->order('apartmentid desc')
            ->select();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function adddormitoryrun()//添加学生寝室操作
    {
        $date = input('post.');
        $validate = new validate([
            ['apartmentid', 'require|number', '所属分类名称不能为空|所属分类参数错误，请返回重试！'],
            ['dormitoryinfo', 'require|number', '操作类型不能为空|操作类型参数错误，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加学生寝室号时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checksecin = Db::table('dormitory')
                ->where('apartmentid', $date['apartmentid'])
                ->where('dormitoryinfo', $date['dormitoryinfo'])
                ->select();//判断专业和学院是否相符
            if ($checksecin) {
                $this->error("似乎已经在系统中已经存在这个寝室号:{$date['dormitoryinfo']}");
            }
            $scoresecrun = Db::table('dormitory')->insert($date);
            if ($scoresecrun) {
                $syslog = ['ip' => $ip = request()->ip(),
                    'datetime' => $time = date('Y-m-d H:i:s'),
                    'info' => '添加了学生寝室号为：' . $date['dormitoryinfo'] . ' 的信息。',
                    'state' => '正常',
                    'username' => $usrlogo = session('username'),];
                Db::table('systemlog')->insert($syslog);
                echo "<script type='text/javascript'>parent.layer.alert('学生寝室号添加成功！');parent.history.go(-1);</script>";
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('学生寝室号添加失败！');parent.history.go(-1);</script>";
            }
        }
    }

    public function apartmentlist()//公寓表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("apartment_view")
            ->count("apartmentid");
        $cate_list = Db::name("apartment_view")
            ->limit($start, $limit)
            ->order("apartmentid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }
        return json($list);
    }

    public function apartmentcheck(Request $request)//公寓表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("apartment_view")
            ->where('apartmentinfo|apartmentid', 'like', "%" . $date["apartmentinfo"] . "%")
            ->count("apartmentid");
        $cate_list = Db::name("apartment_view")
            ->where('apartmentinfo|apartmentid', 'like', "%" . $date["apartmentinfo"] . "%")
            ->limit($start, $limit)
            ->order("apartmentid desc")
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

    public function dormitorylist()//寝室表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("dormitory_view")
            ->count("dormitoryid");
        $cate_list = Db::name("dormitory_view")
            ->limit($start, $limit)
            ->order("dormitoryid desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";//返回数据给前端
        }
        return json($list);
    }

    public function dormitorycheck(Request $request)//寝室表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("dormitory_view")
            ->where('dormitoryinfo|apartmentinfo', 'like', "%" . $date["dormitoryinfo"] . "%")
            ->count("dormitoryid");
        $cate_list = Db::name("dormitory_view")
            ->where('dormitoryinfo|apartmentinfo', 'like', "%" . $date["dormitoryinfo"] . "%")
            ->limit($start, $limit)
            ->order("dormitoryid desc")
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

    public function showapartment()//查看公寓页面
    {
        $date = input('get.');
        $result1 = Db::name("apartment_view")
            ->where('apartmentid', $date["id"])
            ->find();//使用find前端可以直接输出
        $result2 = Db::table('apartment')
            ->select();//判断
        $this->assign('data', $result1);
        $this->assign('data2', $result2);
        return $this->fetch();
    }

    public function editapartment()//编辑公寓操作
    {
        $date = input('post.');
//        return json($date);
        $validate = new validate([
            ['collegeid', 'require|number', '所属单位参数异常，请返回重试！|所属单位参数异常，请返回重试！'],
            ['apartmentinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
            ['apartmentid', 'require|number', '所属一级分类参数异常，请返回重试！|所属一级分类参数异常，请返回重试！']
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑公寓信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

            $scorefircheck = Db::table('apartment_view')
                ->where('apartmentid', $date['apartmentid'])
                ->where('apartmentinfo', $date['apartmentinfo'])
                ->select();//判断(w_tip:此处应该是判断消息是否重复)
    //            return json (!$scorefircheck);
            if (!$scorefircheck) {
                $scorefir = Db::table('apartment')
                    ->where('apartmentid', $date['apartmentid'])
                    ->update([
                        'apartmentid' => $date['apartmentid'],
                        'apartmentinfo' => $date['apartmentinfo']
                    ]);//修改操作
                if ($scorefir) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '编辑公寓信息为' . $date['apartmentinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('公寓信息修改成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('公寓信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
            }

        }

    }

    public function delapartment()//删除公寓分类
    {
        $data = input('post.');
        $res = Db('apartment')->where('apartmentid', $data['apartmentid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function showdormitory()//查看学生寝室页面
    {
        $date = input('get.');
        $result1 = Db::name("dormitory_view")
            ->where('dormitoryid', $date["id"])
            ->find();//使用find前端可以直接输出
        $result2 = Db::table('apartment')
            ->select();//判断
        $this->assign('data', $result1);
        $this->assign('data2', $result2);
        return $this->fetch();
    }

    public function editdormitory()//编辑学生寝室操作
    {
        $date = input('post.');
        $validate = new validate([
            ['dormitoryid', 'require|number', '操作参数异常，请返回重试！|操作参数异常，请返回重试！'],
            ['apartmentid', 'require|number', '所属一级分类参数异常，请返回重试！|所属一级分类参数异常，请返回重试！'],
            ['dormitoryinfo', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '描述内容不能为空！|描述包含非法字符！|描述输入内容过长！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑学分操作二级分类时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

            $scoreseccheck = Db::table('dormitory_view')
//                ->where('dormitoryid',$date['dormitoryid'])
                ->where('dormitoryinfo', $date['dormitoryinfo'])
                ->select();//判断
            if (!$scoreseccheck) {
                $scoresec = Db::table('dormitory')
                    ->where('dormitoryid', $date['dormitoryid'])
                    ->update([
                        'apartmentid' => $date['apartmentid'],
                        'dormitoryinfo' => $date['dormitoryinfo'],
                    ]);//修改操作
                if ($scoresec) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '编辑了学生寝室为' . $date['dormitoryinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('学生寝室信息未修改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
            }

        }
    }

    public function deldormitory()//删除学生寝室
    {
        $data = input('post.');
        $res = Db('dormitory')->where('dormitoryid', $data['dormitoryid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function departmentadmin()//部门和单位管理页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看所有部门/单位信息。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function deldep()//删除学分操作二级分类
    {
        $data = input('post.');
        $res = Db('college')->where('collegeid', $data['collegeid'])->delete();
        if ($res) {
            $status = '1';
        } else {
            $status = '0';
        }
        exit(json_encode($status));
    }

    public function adddepartment()//添加部门/单位页面
    {
        return $this->fetch();
    }

    public function adddepartmentrun()//添加部门/单位操作
    {
        $data = input('post.');
        $dataclass = [
            'class' => '2',
        ];
        $date = $data + $dataclass;
        $validate = new validate([
            ['collegeinfo', 'require|chs|max:60', '部门/单位名称不能为空|部门/单位名称限制位20字以内全汉字|部门/单位名称限制位20字以内全汉字'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '添加部门/单位信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $departmencheck = Db::table('college')
                ->where('collegeinfo', $date['collegeinfo'])
                ->where('class', '2')
                ->select();//用户名重复性检测
            if ($departmencheck) {
                echo "<script type='text/javascript'>parent.layer.alert('该部门/单位信息已经存在，请返回重试！');parent.history.go(-1);</script>";
            } else {
                $departmenrun = Db::table('college')->insert($date);
                if ($departmenrun) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '添加了部门/单位名称为：' . $date['collegeinfo'] . ' 的信息。',
                        'state' => '正常',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('部门/单位信息添加成功！');parent.history.go(-1);</script>";
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('部门/单位信息添加失败！');parent.history.go(-1);</script>";
                }
            }
        }
    }

    public function departmentlist()//部门/单位表格后台
    {
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("college")
            ->where('class', '2')
            ->count("collegeid");
        $cate_list = Db::name("college")
            ->limit($start, $limit)
            ->where('class', '2')
            ->order('collegeid desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;

        return json($list);
    }

    public function departmentcheck(Request $request)//部门/单位表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("college")
            ->where('collegeinfo', 'like', "%" . $date["departmentinfo"] . "%")
            ->where('class', '2')
            ->count("collegeid");
        $cate_list = Db::name("college")
            ->where('collegeinfo', 'like', "%" . $date["departmentinfo"] . "%")
            ->where('class', '2')
            ->limit($start, $limit)
            ->order("collegeid desc")
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

    public function showdepartment()//查看部门/单位信息
    {
        $date = input('get.');
        $result = Db::name("college")
            ->where('collegeid', $date["id"])
            ->where('class', '2')
            ->find();//使用find前端可以直接输出

        $this->assign('data', $result);

        return $this->fetch();
    }

    public function editdepartment()//部门/单位编辑操作
    {
        $date = input('post.');
        $validate = new validate([
            ['collegeinfo', 'require|chs|max:60', '部门/单位名称不能为空！|部门/单位名称为20位以内全汉字|部门/单位名称为20位以内全汉字'],
            ['collegeid', 'require|number', '部门/单位参数异常，请返回重试！|部门/单位参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $syslog = ['ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '编辑部门/单位信息时输入非法字符。',
                'state' => '异常',
                'username' => $usrlogo = session('username'),];
            Db::table('systemlog')->insert($syslog);
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $departmentcheck = Db::table('college')
                ->where('collegeid', $date['collegeid'])
                ->where('class', '2')
                ->select();//判断
            if ($departmentcheck) {
                $class = Db::table('college')
                    ->where('collegeid', $date['collegeid'])
                    ->update([
                        'collegeinfo' => $date['collegeinfo']
                    ]);//修改操作
                if ($class) {
                    $syslog = ['ip' => $ip = request()->ip(),
                        'datetime' => $time = date('Y-m-d H:i:s'),
                        'info' => '修改了部门/单位信息为：' . $date['collegeinfo'] . '。',
                        'state' => '重要',
                        'username' => $usrlogo = session('username'),];
                    Db::table('systemlog')->insert($syslog);
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('部门/单位名称未更改！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }
            } else {
                echo "<script type='text/javascript'>parent.layer.alert('部门/单位参数错误，请返回重试！');parent.history.go(-1);</script>";
                exit;
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
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("stu_view")
            ->where('s_id|s_name|teacherinfo', 'like', "%" . $date["stuname"] . "%")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            ->where('s_id|s_name|teacherinfo', 'like', "%" . $date["stuname"] . "%")
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
            $result1 = Db::name("user_view")
                ->where('username', $usrname)
                ->find();//使用find前端可以直接输出
            $this->assign('data', $result1);
            $result2 = Db::name("stu_view")
                ->where('s_id', $stu["id"])
                ->find();//使用find前端可以直接输出
            $this->assign('data2', $result2);
            $result3 = Db::name("scorefirst")
                ->select();
            $this->assign('data3', $result3);
            return $this->fetch();
        }

    }

    public function scoresec()//二级联动---二级分类
    {
        $scoresec = input('get.');
        $score = Db::name("scoresec")
            ->where('scorefirid', $scoresec['q'])
            ->select();
        $count = Db::name("scoresec")
            ->where('scorefirid', $scoresec['q'])
            ->count("scorefirid");

        echo "<select name='opscoresec'>";

        foreach ($score as $value) {
            echo "<option value='{$value['scoresecid']}' name='opscoresec'>{$value['scoresecinfo']} 分数上限：{$value['score']}</option>11";
        }
        echo "</select>";
    }

    public function scoreoperationrun()//学分操作后台
    {
        $date = input('post.');
        $time = date('Y-m-d H:i:s');
        $ip = request()->ip();
        $operinfo = [
            'ip' => $ip,
            'datetime' => $time,
            'opstate' => '1',
            'otherstate' => '0',
        ];
        $data = $date + $operinfo;
        $validate = new validate([
            ['stuid', 'require|number|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
            ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
            ['opscorefir', 'require|number', '请选择一级分类！|一级分类参数错误，请返回重试！'],
            ['opscoresec', 'require|number', '请选择二级分类！|二级分类参数错误，请返回重试！'],
            ['opscoreclass', 'require|number', '请选择操作类型！|操作类型参数错误，请返回重试！'],
            ['score', 'require|number', '请选择操作分数！|操作分数参数错误，请返回重试！'],
        ]);
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
            ->limit($start, $limit)
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
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("score_view")
            ->where('opstate', '2')//根据权限修改where条件
            ->where('id|s_id|s_name|scoresecinfo', 'like', "%" . $date["id"] . "%")
            ->count("id");
        $cate_list = Db::name("score_view")
            ->where('opstate', '2')//根据权限修改where条件
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
            ['id', 'require|number', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('zlog_view')
                ->where('id', $date['id'])
                ->find();//通过session查询个人信息

            $this->assign('data', $result);
            return $this->fetch();
        }
    }

   public function examinerun()//审核操作
    {
        $data = input('post.');
        $stateupdate = [
            'opstate' => '1',
          ];//#########################################根据权限需要修改一下代码块的相关代表状态的参数
          $date = $data + $stateupdate;
          //$a = $data['s_id'];
          //echo "<script>alert('$a')</script>";
        $validate = new validate([
            ['opstate','require|number','请选择操作类型！|操作当前状态参数异常，请返回重试！'],
            ['info','require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100','备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id','require|number','请选择操作类型！|a，请返回重试！'],
            ['username','require|alphaDash','b，请返回重试！|c，请返回重试！'],
            ['othername','require|chs','d，请返回重试！|e，请返回重试！'],
            ['s_id','require|number','f，请返回重试！|g，请返回重试！'],
            ['classinfo','require|chs','h，请返回重试！|i，请返回重试！'],
            ['score','require|number','j，请返回重试！|k，请返回重试！'],
            ]);
        if (!$validate->check($date)){
           
          $msg = $validate->getError();
            echo  "<script>parent.layer.alert('$msg');parent.history.go(-1)</script>";
             exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                    ->where('id',$date['id'])
                    ->find();
              if($checkclass['opstate'] =='1'){
                echo  "<script>parent.layer.alert('系统不允许重复操作！');parent.history.go(-1)</script>";
                exit;
              }
                else{
                    if($checkclass['opstate'] =='5'){
                        echo  "<script>parent.layer.alert('系统不允许重复操作！');parent.history.go(-1)</script>";
                        exit;
                      }
                    }

                    $checkusr = Db::table('user')
                    ->where('username',$date['username'])
                    ->where('u_name',$date['othername'])
                    ->select();//用户名重复性检测
              if($checkusr){
                $time = date('Y-m-d H:i:s');
                $editscore=Db::table('scoreoperation')
                ->where ('id',$date['id'])
                ->update([
                    'opstate'      => $date['opstate'],
                    'othername'       => $date['othername'],
                    'othertime'       =>  $time,
                    'otherstate'       =>  $date['opstate'],
                    'info'          => $date['info']]);//修改操作
        if($editscore){
            if($date['opstate']=='1'){
                if($date['classinfo'] == '加分'){
                    $opres = Db::table('students')->where('s_id',$date['s_id'])->setInc('score',$date['score']);
                    echo"<script>parent.layer.alert('操作成功！');parent.history.go(-1);</script>";
                     exit;
                }else{
                    $opres = Db::table('students')->where('s_id',$date['s_id'])->setDec('score',$date['score']);
                    echo"<script>parent.layer.alert('操作成功！');parent.history.go(-1);</script>";
                     exit;
                }
            }
          if($date['opstate']=='5'){
                    $opres = Db::table('students')->where('s_id',$date['s_id'])->setInc('score',0);
                    echo"<script>parent.layer.alert('操作成功！');parent.history.go(-1);</script>";
                     exit;
           
            }
        } else {
          echo"<script>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
            exit;//判断更新操作是否成功
        }
                    }else{
                        echo"<script>parent.layer.alert('参数错误！');parent.history.go(-1);</script>";
                        exit;  
                    }
        
    }
    }

//日志模块模块开始-------------------------------------------------------------------》
    public function systemlog()//系统操作日志页面
    {
        $syslog = ['ip' => $ip = request()->ip(),
            'datetime' => $time = date('Y-m-d H:i:s'),
            'info' => '查看系统日志。',
            'state' => '正常',
            'username' => $usrlogo = session('username'),];
        Db::table('systemlog')->insert($syslog);
        return $this->fetch();
    }

    public function systemloglist()//系统操作日志表格后台
    {
        $usrname = session('username');
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("systemlog")
            ->count("id");
        $cate_list = Db::name("systemlog")
            ->limit($start, $limit)
            ->order('datetime desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function systemlogload(Request $request)//系统操作日志表格重载
    {
        $date = $request->post();
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("systemlog")
            ->where('username|ip|info|state', 'like', "%" . $date['id'] . "%")
            ->count("id");
        $cate_list = Db::name("systemlog")
            ->where('username|ip|info|state', 'like', "%" . $date['id'] . "%")
            ->limit($start, $limit)
            ->order("datetime desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

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
        $page = input("get.page") ? input("get.page") : 1;
        $page = intval($page);
        $limit = input("get.limit") ? input("get.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("zlog_view")
            ->count("id");
        $cate_list = Db::name("zlog_view")
            ->limit($start, $limit)
            ->order('id desc')
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
        $count = Db::name("zlog_view")
            ->where('id|s_name|s_class|scoresecinfo|s_id', 'like', "%" . $date["log"] . "%")
            ->count("id");
        $cate_list = Db::name("zlog_view")
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
            ['id', 'require|number', '参数异常，请返回重试！|参数异常，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $result = Db::table('zlog_view')
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
            ['opstate', 'require|number', '请选择操作类型！|参数异常，请返回重试！'],
            ['info', 'require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100', '备注不能为空|备注包含非法字符！|备注最多只能输入100个字符！'],
            ['id', 'require|number', '请选择操作类型！|参数异常，请返回重试！'],
            ['username', 'require|alphaDash', '参数异常，请返回重试！|参数异常，请返回重试！'],
            ['othername', 'require|chs', '参数异常，请返回重试！|参数异常，请返回重试！'],

        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script type='text/javascript'>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $checkclass = Db::table('scoreoperation')
                ->where('opstate', '4')
                ->where('id', $date['id'])
                ->select();
            if ($checkclass) {
                echo "<script type='text/javascript'>parent.layer.alert('该操作已被撤销，请勿重复提交相同操作！');parent.history.go(-1)</script>";
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
                    echo "<script type='text/javascript'>parent.layer.alert('保存成功！');parent.history.go(-1);</script>";
                    exit;
                } else {
                    echo "<script type='text/javascript'>parent.layer.alert('参数错误，请返回重试！');parent.history.go(-1);</script>";
                    exit;//判断更新操作是否成功
                }

            }
        }

    }


}