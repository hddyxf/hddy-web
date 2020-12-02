<?php


namespace app\index\validate;


class Scoresec extends \think\Validate
{
    protected $rule=[
        'scorefirid','regex:int','一级分类必须为正整数'
    ];
}