<?php


namespace app\index\validate;


use think\Validate;

class Wechat2 extends Validate
{
    protected $rule = [
        ['AuthID'  =>  'require|max:1|regex:int','权限ID为空，请返回重试！|权限ID最大限制为1位|权限ID必须为纯数字'],
        ['LimtParam' =>  'require|max:10|regex:int','限制参数为空，请返回重试！|限制参数最大为10位|限制参数必须为纯数字'],
        ['stuId', 'require|regex:int|max:15', '学生ID为空，请返回重试！|学生ID必须为纯数字|学生ID最大15位'],
        ['opusername', 'require|alphaDash|max:15', '操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！|操作人信息参数错误，请返回重试！'],
        ['opscorefir', 'require|regex:int', '请选择一级分类！|一级分类参数错误，请返回重试！'],
        ['opscoresec', 'require|regex:int', '请选择二级分类！|二级分类错误，请返回重试！'],
        ['opscoreclass', 'require', '操作类型为空'],
        ['score', 'require|regex:int', '请选择操作分数！|操作分数参数错误，请返回重试！'],
    ];
}