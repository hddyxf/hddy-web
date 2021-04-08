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
    public function translateinfo($key,$data){
        $rule=$this->rule;
        $res=Db::name($rule[$key]['table'])
            ->where($rule[$key]['forkey'],$data)
            ->value($rule[$key]['end']);
        return $res;
    }
}