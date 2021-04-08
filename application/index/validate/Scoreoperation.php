<?php


namespace app\index\validate;


use think\Validate;

class Scoreoperation extends Validate
{
    protected $rule=[//验证规则
        'stuid|学号'=>['require','regex:int','max:12'],
        'stuname|学生姓名'=>['require','chs','max:5'],
        'stusex|学生性别'=>['require','regex:u_sex'],
        'stuclass|学生班级'=>['require','regex:int','max:10'],
        'opcollege|学院ID'=>['require','regex:int'],
        'opmajor|专业ID'=>['require','regex:int'],
        'opteacher|教师ID'=>['require','regex:int'],
        'opusername|用户名'=>['require','chs','max:5'],
        'opjurisdiction|权限ID'=>['require','regex:int'],
        'opname|用户姓名'=>['require','chs'],
        'opscoreclass|操作类型'=>['require','regex:int'],
        'score|操作分数'=>['require','regex:int'],
        'opscorefir|一级操作分类'=>['require','regex:int'],
        'opscoresec|二级操作分类'=>['require','regex:int'],
        'opstate|操作异常参数'=> ['require|regex:int'],
        'info|操作信息'=> ['require|/^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u|max:100'],
        'id|学分操作id'=> ['require|regex:int'],
        'username|用户名'=> ['require|alphaDash'],
        'othername|操作名'=>[ 'require|chs'],
    ];
    protected $message=[//如果验证错误提示信息
        'stuid.regex:int'=>'学号格式错误',
        'stuname.chs'=>'学生姓名格式错误',
        'stusex.regex:u_sex'=>'学生性别格式错误',
        'stuclass.regex:int'=>'学生班级格式错误',
        'opcollege.regex:int'=>'学院ID格式错误',
        'opmajor.regex:int'=>'专业ID格式错误',
        'opteacher.regex:int'=>'教师ID格式错误',
        'opjurisdiction.regex:int'=>'权限ID格式错误',
        'opscoreclass.regex:int'=>'操作类型格式错误',
        'score.regex:int'=>'操作分数格式错误',
        'opstate.regex:int'=>'操作当前状态参数异常，请返回重试',
        'info./^[A-Za-z0-9，,。.\x{4e00}-\x{9fa5}]+$/u'=>'备注包含非法字符！',
        'id.regex:int'=>'参数异常，请返回重试！',
        'username.regex:int'=>'用户名异常，请返回重试！',
        'othername.regex:int'=>'操作名异常，请返回重试！'
    ];
}