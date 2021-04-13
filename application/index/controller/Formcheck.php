<?php


namespace app\index\controller;


use app\index\model\Students;
use think\Controller;
use think\Db;
use app\index\model\User;

class   Formcheck extends Controller
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
            'u_id'=>'用户ID重复请重新输入',
            'username'=>'用户名重复请重新输入',
            'user_id'=>'身份证号重复请重新输入',
        );
        foreach ($checkey as $key=>$value){
            if ($data[$value]==null){
                unset($checkey[$key]);
            }
        }
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
            'u_id'=>'用户ID重复请重新输入',
            'username'=>'用户名重复请重新输入',
            'user_id'=>'身份证号重复请重新输入'
        );
        foreach ($checkey as $key=>$value){
            if ($data[$value]==null){
                unset($checkey[$key]);
            }
        }
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

    /*
     * selef_user是自己的username
     * or_user 是要插销的操作相关的权限的username
     * */
    public  function  auth_check($self_user,$or_user){
       $or_jur= Db::name('user_view')
            ->where('username',$or_user)
            ->value('jurisdiction');
        $self_jur=Db::name('user_view')
            ->where('username',$self_user)
            ->value('jurisdiction');
        if ($self_jur<$or_jur){
            return array('code'=>'1','res'=>true,'orinfo'=>$or_jur,'selfinfo'=>$self_jur);
        }elseif ($self_jur>$or_jur){
            return array('code'=>'2','res'=>false,'orinfo'=>$or_jur,'selfinfo'=>$self_jur);
        }else{
            return array('code'=>'3','res'=>false,'orinfo'=>$or_jur,'selfinfo'=>$self_jur);
        }
    }
//$username,$limit_table,$limit_data,$sid
    public static function limit_select_user($data){
        if($data['jur']==9){
            $res=Db::name('stu_view')
                ->where('s_id',$data['s_id'])
                ->where('apartment','<>',null)
                ->find();
        }elseif($data['jur']==2) {
            $res = Db::name('stu_view')
                ->where('s_id', $data['s_id'])
                ->find();
        }else {
            $limit = Db::name($data['limit_table'])
                ->where('username', $data['username'])
                ->find();
            $res = Db::name('stu_view')
                ->where('s_id', $data['sid'])
                ->where($data['limit_data'], $limit[$data['limit_data']])
                ->find();
        }
        return $res;
    }
    public static function systemlogs($data){
        $date=array([
            'ip' => $data[0],
                'datetime' => $data[1],
                'info' => $data[2] . $data[3] . $data[4],
                'state' => '异常',
                'username' => session('username'),
        ]);
        halt($date);
        return $date;
    }
}