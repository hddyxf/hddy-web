<?php


namespace app\index\controller;


use app\index\model\Students;
use think\Controller;
use think\Db;
use app\index\model\User;

class Formcheck extends Controller
{
    /*$data为你要查重的数据
     * $table为你要查重的表
     * $checkey为你要查重的字段
     * */
    public function check_addstu($data,$table,$checkey){
        //对学号、身份证号，学生手机号，寝室号及床位号进行查重（s_id,s_proid,s_add,s_room）
        $msg=array(
           's_id'=>'学号重复请重新输入',
           's_proid'=>'身份证重复请重新输入',
           's_add'=>'个人手机号码重复请重新输入',
           's_room'=>'寝室号及床位号重复请重新输入',
            'username'=>'用户名重复请重新输入',
            'user_id'=>'用户身份证重复请重新输入',
            'add'=>'用户手机号重复请重新输入',
            'qq'=>'用户QQ号重复请重新输入',
            'vx'=>'微信号重复请重新输入',
            'u_mail'=>'邮件号重复请重新输入',
            'teacheradd'=>'教师手机号码重复请重新输入',
        );
        foreach ($checkey as $key => $value){
            $res=Db::name($table)
                ->where($value,$data[$value])
                ->find();
            if ($res){
                return array('code'=>1,'msg'=>$msg[$value]);
//                return $res;
            }
        }
        return false;
    }
    /*$data为你要查重的数据
     * $table为你要查重的表
     * $checkey为你要查重的字段
     * $pk为你要排除信息簇的主键
     * */
    public function check_stuinfo($data,$table,$checkey,$pk){
        //对学号、身份证号，学生手机号，寝室号及床位号进行查重（s_id,s_proid,s_add,s_room）
        $msg=array(
            's_id'=>'学号重复请重新输入',
            's_proid'=>'身份证重复请重新输入',
            's_add'=>'个人手机号码重复请重新输入',
            's_room'=>'寝室号及床位号重复请重新输入',
            'username'=>'用户名重复请重新输入',
            'user_id'=>'用户身份证重复请重新输入',
            'add'=>'用户手机号重复请重新输入',
            'qq'=>'用户QQ号重复请重新输入',
            'vx'=>'微信号重复请重新输入',
            'u_mail'=>'邮件号重复请重新输入',
            'teacheradd'=>'教师手机号码重复请重新输入',
        );
        foreach ($checkey as $key => $value){
            $res=Db::name($table)
                ->where($value,$data[$value])
                ->where($pk,'<>',$data[$pk])
                ->find();
            if ($res){
                return array('code'=>1,'msg'=>$msg[$value]);
//                return $res;
            }
        }
        return false;
    }
}