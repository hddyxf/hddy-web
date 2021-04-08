<?php


namespace app\index\validate;


class Teacher extends \think\Validate
{
    protected $rule=[//验证规则
        'teacherinfo|老师姓名'=>['require','chs','max:5'],
        'collegeid|学院ID'=>['require','regex:int'],
        'teachersex|性别'=>['require','regex:u_sex'],
        'teacheradd|手机号码'=>['require','regex:add']
    ];
    protected $message=[//错误提示语句
        'collegeid.regex:int'=>'学院ID格式错误',
        'teachersex.regex:u_sex'=>'性别格式错误',
        'teacheradd.regex:add'=>'手机号码格式错误',
    ];
}