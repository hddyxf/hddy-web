<?php
namespace app\index\validate;
use think\Validate;

class Validate extends Validate{
    protected $rule = [
        'add'   => 'require|max:11',
        'email' => 'email',
        'qq'    => 'require|max:15',
    ];
    protected $msg = [
         'add.require'=>'联系方式限制不能为空',
         'add.number' =>'联系方式限制全部为数字',
         'add.max'    =>'联系方式限制不能超过11位',
         'qq.number'  =>'qq号码限制全部为数字',
         'qq.max'     =>'qq号码限制不能超过11位',
         'email'      =>'邮箱格式不正确',
    ];
} 