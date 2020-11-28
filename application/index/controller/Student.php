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
class Student extends Controller//权限1
{
    protected function _initialize()
    {
        $usrname = session('username');
        if (empty($usrname)) {
            $this->error('非法登陆或登陆超时！', 'Admin/login');
            exit;
        }
    }

    public function goout()//退出
    {
        session('username', null);
        $this->success('退出成功', 'Admin/login');
    }

    public function studentindex()
    {
        $usrname = session('username');
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->find();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function stulog()//个人操作日志页面
    {
        $usrname = session('username');
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->find();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function stuloglist()
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
            ->order('datetime desc')
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);
    }

    public function stulogcheck(Request $request)
    {
        $usrname = session('username');
        $date = $request->post();
//        $userinfo=Db::name('user_view')//找到这个用户
//            ->where('user_id',)
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("zlog_view")
            ->where('username', $usrname)
            ->where('id|s_name|scoresecinfo|s_id|s_class', 'like', "%" . $date["log"] . "%")
            ->count("id");
        $cate_list = Db::name("zlog_view")
            ->where('username', $usrname)
            ->where('id|s_name|scoresecinfo|s_id|s_class', 'like', "%" . $date["log"] . "%")
            ->limit($start, $limit)
            ->order("id desc")
            ->select();
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        return json($list);//返回数据给前端
    }

    public function stuoperation()//学分操作页面
    {
        $usrname = session('username');
        if (empty($usrname)) {
            $this->error("进入学分操作系统失败！");
            exit;
        }
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->find('jurisdiction');
        if ($result['jurisdiction'] == '7') {
            $this->success("即将进入学分操作系统！", 'stusch');
        } else {
            $this->success("即将进入学分操作系统！", 'stucoll');
        }
    }

    public function scoreoperationlist()//初始化学分操作页面表格
    {
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = "0";
        return json($list);
    }

    public function stusch()
    {
        $usrname = session('username');
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->find();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function stucoll()
    {
        $usrname = session('username');
        $result = Db::table('user_view')
            ->where('username', $usrname)
            ->find();
        $this->assign('data', $result);
        return $this->fetch();
    }

    public function stucollop(Request $request)
    {
        $date = $request->post();
        $username = session('username');

        //判断权限
        $userinfo_1 = Db::name('user_stu_view')
            ->where('username', $username)
            ->find();
        $userinfo_2 = Db::name('user_view')
            ->where('username', $username)
            ->find();
        $flag = empty($userinfo_1) ? 1 : 0;

        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("stu_view")
//             ->where('collegeid',$usrcollege)
            ->where('s_id|s_name|s_class|dormitoryinfo', 'like', "%" . $date["stuname"] . "%")
            ->count("s_id");
        if ($flag) {
            $cate_list = Db::name("stu_view")
//             ->where('collegeid',$usrcollege)
                ->where('s_id|s_name|s_class|dormitoryinfo', 'like', "%" . $date["stuname"] . "%")
                ->limit($start, $limit)
                ->order("s_id desc")
                ->select();
        } else {
            $cate_list = Db::name("stu_view")
//             ->where('collegeid',$usrcollege)
                ->where('s_id|s_name|s_class|dormitoryinfo', 'like', "%" . $date["stuname"] . "%")
                ->where('s_class', $userinfo_1['s_class'])
                ->limit($start, $limit)
                ->order("s_id desc")
                ->select();
        }
        $list["msg"] = "";
        $list["code"] = 0;
        $list["count"] = $count;
        $list["data"] = $cate_list;
        if (empty($cate_list)) {
            $list["msg"] = "暂无数据";
        }
        return json($list);//返回数据给前端
    }

    public
    function stuschop(Request $request)
    {
        $date = $request->post();
//        return 1;
        $usrname = session('username');
        $usrinfo = Db::table('user_stu_view')
            ->where('username', $usrname)
            ->find();
        $usrcollege = $usrinfo['u_classinfo'];
        $page = input("post.page") ? input("post.page") : 1;
        $page = intval($page);
        $limit = input("post.limit") ? input("post.limit") : 1;
        $limit = intval($limit);
        $start = $limit * ($page - 1);
        //分页查询
        $count = Db::name("stu_view")
            ->where('collegeid', $usrcollege)
            ->where('s_class', $usrinfo['s_class'])
            ->where('s_id|s_name|s_class|dormitoryinfo', 'like', "%" . $date["stuname"] . "%")
            ->count("s_id");
        $cate_list = Db::name("stu_view")
            ->where('collegeid', $usrcollege)
            ->where('s_class', $usrinfo['s_class'])
            ->where('s_id|s_name|s_class|dormitoryinfo', 'like', "%" . $date["stuname"] . "%")
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

    public
    function stucollshowstu()
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
                // ->where('collegeid', $classid)
                ->select();
            $this->assign('data3', $result3);
            return $this->fetch();
        }
    }

    public
    function stucollshowstu1()
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
                ->select();
            $this->assign('data3', $result3);
            return $this->fetch();
        }
    }

    public
    function scoresec()//二级联动---二级分类
    {
        $scoresec = input('get.');
        $score = Db::name("scoresec_view")
            ->where('scorefirid', $scoresec['q'])
            ->select();
        $count = Db::name("scoresec")
            ->where('scorefirid', $scoresec['q'])
            ->count("scorefirid");
        return json($score);
        echo "<select name='opscoresec'>";

        foreach ($score as $value) {
            echo "<option value='{$value['scoresecid']}' name='opscoresec'>{$value['scoresecinfo']} 分数上限：{$value['score']}</option>11";
        }
        echo "</select>";
    }

    public
    function scoreoperationrun()//学分操作后台
    {
        $date = input('post.');
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
        $validate = new validate([
            ['stuid', 'require|regex:int|max:15', '学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！|学生信息参数错误，请返回重试！'],
            ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
            ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
            ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类参数错误，请返回重试！'],
            ['opscoreclass', 'require|regex:int', '请选择操作类型！|操作类型参数错误，请返回重试！'],
            ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
        ]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            echo "<script>parent.layer.alert('$msg');parent.history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {

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
                    $this->success("对学号：{$date['stuid']} 的学生操作已被确认！");
                } else {
                    echo "<script>parent.layer.alert('操作失败，请稍后再试！');parent.history.go(-1);</script>";
                }
            } else {
                echo "<script>parent.layer.alert('操作分数不能高于该操作分数上限！');parent.history.go(-1);</script>";
            }
        }
    }

    public
    function test()
    {
        return session('username');
    }
}