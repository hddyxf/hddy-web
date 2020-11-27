<?php


namespace app\index\controller;

use think\db;
class authcheck extends Controller
{
    /*user为传入的用户名（管理员用户名和学生学号）
    type为查询的是用户表还是学生表
     * */
    function auth_search($user,$type){
        //先获取每个权限个人的权限限制字段值
        $type_key=array('user'=>'username','students'=>'s_id');
        $type_res=array('user'=>'user_id','students'=>'');
        Db::name($type)
            ->where($type_key[$type],$user);

    }
}