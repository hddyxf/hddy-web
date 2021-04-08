<?php

namespace app\index\controller;

use think\Controller;
use app\index\model\User;
use think\Session;
use think\validate;
use think\Db;

class Login extends Controller
{
    public function login()
    {
        $date = input('post.');

        session('ip',$_SERVER['HTTP_HOST']);
//        return json(session('url'));
//        $dateinfo=Db::name('user')
//            ->where('u_id',$date['username'])
//            ->find();
//        $dateinfo2=Db::name('students')
//            ->where('user_id',$dateinfo[''])
        $validate = new validate([
            ['username', 'require|max:20|alphaDash', '用户名不能为空！|用户名长度不能超过20位！|用户名包含非法！'],
            ['password', 'require|alphaDash', '密码不能为空！|密码包含非法字符！'],
            ['code', 'alphaDash|max:4', '验证码包含非法字符！|非法输入！'],]);
        if (!$validate->check($date)) {
            $msg = $validate->getError();
            $syslog = [
                'ip' => $ip = request()->ip(),
                'datetime' => $time = date('Y-m-d H:i:s'),
                'info' => '登陆时输入非法字符。',
                'state' => '正常',
                'username' => $date['username'],
            ];
            Db::table('systemlog')->insert($syslog);
            echo "<script>alert('$msg');history.go(-1)</script>";
            exit;//判断数据是否合法
        } else {
            $user = new User();
            if (captcha_check($date['code'])) {
                //$this->success('验证吗正确，');//跳转至相应页面
                $result = $user->where('username', $date['username'])
                    ->where('state', '1')
                    ->find();
                if ($result) {
                    if ($result['password'] === md5($date['password'])) {
                        session('username', $date['username']);
                        $syslog = [
                            'ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '登陆系统。',
                            'state' => '正常',
                            'username' => $date['username'],
                        ];
                        Db::table('systemlog')->insert($syslog);
                        $qxcheck = Db::table('user')
                            ->where('username', $date['username'])
                            ->find();
                        if ($qxcheck['jurisdiction'] == '1'||$qxcheck['jurisdiction'] == '11') {
                            if ($qxcheck['jurisdiction'] == '11'){
                                $this->success("登陆成功!  欢迎：{$date['username']}。", 'Define/hddy');
                                session('qx',$qxcheck['jurisdiction']);
                            }
                            $this->success("登陆成功!  欢迎：{$date['username']}。", 'Hddy1/hddy');
                            session('username', $date['username']);
                            session('login_url',$_SERVER['HTTP_HOST']);
                        } else {
                            if ($qxcheck['jurisdiction'] == '9') {
                                $this->success("登陆成功!  欢迎：{$date['username']}。", 'Apartment/hddy');
                                session('username', $date['username']);
                            } else {
                                if ($qxcheck['jurisdiction'] == '7' || $qxcheck['jurisdiction'] == '10') {
                                    $this->success("登陆成功!  欢迎：{$date['username']}。", 'Student/studentindex');
                                    session('username', $date['username']);
                                    session('user_id',$date[]);
                                    session('login_url',$_SERVER['HTTP_REFERER']);
                                } else {
                                    if ($qxcheck['jurisdiction'] == '6') {
                                        $this->success("登陆成功!  欢迎：{$date['username']}。", 'Instructor/hddy');
                                        session('username', $date['username']);
                                    } else {
                                        if ($qxcheck['jurisdiction'] == '5') {
                                            $this->success("登陆成功!  欢迎：{$date['username']}。", 'Instructordoub/hddy');
                                            session('username', $date['username']);
                                        } else {
                                            if ($qxcheck['jurisdiction'] == '4') {
                                                $this->success("登陆成功!  欢迎：{$date['username']}。", 'College/hddy');
                                                session('username', $date['username']);
                                            } else {
                                                if ($qxcheck['jurisdiction'] == '3') {
                                                    $this->success("登陆成功!  欢迎：{$date['username']}。", 'Work/hddy');
                                                    session('username', $date['username']);
                                                } else {
                                                    if ($qxcheck['jurisdiction'] == '2') {
                                                        $this->success("登陆成功!  欢迎：{$date['username']}。", 'Work/hddy');
                                                        session('username', $date['username']);
                                                    } else {
                                                        $this->error("系统内部错误，请联系管理员！");
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    } else {
                        $syslog = [
                            'ip' => $ip = request()->ip(),
                            'datetime' => $time = date('Y-m-d H:i:s'),
                            'info' => '使用错误的密码尝试登陆系统。',
                            'state' => '异常',
                            'username' => $date['username'],
                        ];
                        Db::table('systemlog')->insert($syslog);
                        $this->error('用户名或密码错误！');
                        exit;
                    }
                } else {
                    echo "<script>alert('用户名不存在或已停用！');history.go(-1);</script>";
                    //$this->error('用户名不存在');
                    exit;
                }
            } else {
                echo "<script>alert('验证码错误！');history.go(-1);</script>";
                //$this->error('验证吗错误！');
                exit;
            }

        }
    }

}

