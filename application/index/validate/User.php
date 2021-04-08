<?php


namespace app\index\validate;
use think\Validate;


class User extends Validate
{
    //自定义验证规则
    protected $rule = [
        'u_mail|邮件'=>['require','regex:new_eamil','max:30'],
      'username|姓名' => ['require','alphaDash'],
        'password|密码' => ['require','alphaDash'],
        'u_name|用户姓名'=>['require','regex:u_name'],
        'u_sex|用户性别'=>['require','regex:u_sex'],
        'u_class|部门类别'=>['require','regex:int'],
        'u_classinfo|具体部门'=>['require','regex:int'],
        'jurisdiction|权限级别'=>['require','regex:int'],
        'user_id|身份证'=>['require','regex:user_id'],
        'add|手机号码'=>['require','regex:add'],
        'qq|QQ号'=>['regex:int','max:15'],
        'vx|微信号'=>['regex:fst-a'],
    ];

    //自定义错误提示消息
    protected $msg = [
      'username.require' => '用户名不能为空',
      'username.alphaDash' => '参数异常',
        'password.require' => '密码不能为空',
        'password.alphaDash' => '参数异常',
        'u_mail.regex:new_email'=>'邮件格式错误',
        'u_name.regex:u_name'=>'用户姓名格式错误',
        'u_sex.regex:u_sex'=>'用户性别格式错误',
        'u_class.regex:int'=>'部门类别格式错误',
        'u_classinfo.regex:int'=>'具体部门格式错误',
        'jurisdiction.regex:int'=>'权限级别格式错误',
        'user_id.regex:user_id'=>'身份证格式错误',
        'add.regex:add'=>'手机号码格式错误',
        'qq.regex:int'=>'QQ号码格式错误',
        'vx.regex:fst-a'=>'微信号格式错误',
    ];

    //自定义场景
    protected $scene = [
      'login' => ['username','password'],
      'add' => ['username']
    ];
}