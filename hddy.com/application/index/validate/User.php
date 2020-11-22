<?php


namespace app\index\validate;
use think\Validate;


class User extends Validate
{
    //自定义验证规则
    protected $rule = [
      'username|姓名' => ['require','alphaDash'],
        'password|密码' => ['require','alphaDash'],
    ];

    //自定义错误提示消息
    protected $msg = [
      'username.require' => '用户名不能为空',
      'username.alphaDash' => '参数异常',
        'password.require' => '密码不能为空',
        'password.alphaDash' => '参数异常'
    ];

    //自定义场景
    protected $scene = [
      'login' => ['username','password'],
      'add' => ['username']
    ];
}