<?php


namespace app\index\controller;
use think\Db;
use think\Controller;

class Translate extends Controller
{
    protected $rule=[
        'college'=>['table'=>'college','forkey'=>'collegeid','end'=>'collegeinfo'],
        're_college'=>['table'=>'college','forkey'=>'collegeinfo','end'=>'collegeid'],
        're_major'=>['table'=>'major','forkey'=>'majorinfo','end'=>'majorid'],
        're_teacher'=>['table'=>'teacher','forkey'=>'teacherinfo','end'=>'teacherid']
    ];
    private $FstAuthinfo=[
      'Person'=>'个人管理',
        'Stu'=>'学生管理',
        'Userr'=>'用户管理',
        'Score'=>'学分操作',
        'Sparam'=>'系统参数设置',
        'Logs'=>'日志管理',
    ];
    private $SecAuthinfo=[
        'P1'=>'个人信息',
        'P2'=>'修改密码',
        'P3'=>'个人操作日志',
        'S1'=>'添加学生',
        'S2'=>'批量添加学生',
        'S3'=>'学生信息',
        'U1'=>'添加用户',
        'U2'=>'批量添加用户',
        'U3'=>'批量添加寝室',
        'U4'=>'查看用户',
        'SC1'=>'学分操作',
        'SC2'=>'待审核操作',
        'SP1'=>'学分操作管理',
        'SP2'=>'部门/单位信息管理',
        'SP3'=>'班级信息管理',
        'SP4'=>'公寓信息管理',
        'SP5'=>'辅导员信息管理',
        'SP6'=>'专业信息管理',
        'SP7'=>'学院信息管理',
        'L1'=>'系统操作日志',
        'L2'=>'学分操作日志',
    ];
    private $FstMatchSec=[
        'P1'=>'Person',
        'P2'=>'Person',
        'P3'=>'Person',
        'S1'=>'Stu',
        'S2'=>'Stu',
        'S3'=>'Stu',
        'U1'=>'Userr',
        'U2'=>'Userr',
        'U3'=>'Userr',
        'U4'=>'Userr',
        'SC1'=>'Score',
        'SC2'=>'Score',
        'SP1'=>'Sparam',
        'SP2'=>'Sparam',
        'SP3'=>'Sparam',
        'SP4'=>'Sparam',
        'SP5'=>'Sparam',
        'SP6'=>'Sparam',
        'SP7'=>'Sparam',
        'L1'=>'Logs',
        'L2'=>'Logs',
    ];
    public function translateinfo($key,$data){
        $rule=$this->rule;
        $res=Db::name($rule[$key]['table'])
            ->where($rule[$key]['forkey'],$data)
            ->value($rule[$key]['end']);
        return $res;
    }
    //负责翻译活权限一二级功能模块信息，并转化为数组
    public function translateauinfo($FstAuth,$SecAuth){
        //转化为数组
        $FstAuth = explode(',',$FstAuth);
        $SecAuth = explode(',',$SecAuth);
        //遍历数组得到'一级功能模块'=>'二级功能模块数组'的二维数组
        array_map('IdxtoGl',$FstAuth);
    }
    public function IdxtoGl($val){
        return array($val=>null);
    }
}