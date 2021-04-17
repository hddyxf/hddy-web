<?php


namespace app\index\controller;


use think\Db;
use think\Exception;

class Wechat2
{
    //小程序学分查询模块--------------------------------》开始

    private $exchgFst=array('collegeid_0','teacherid','s_apartment');

    public function selectStuInfo(){
        try {
            if (request()->param('authInfo')==0||request()->param('authInfo')==1||request()->param('authInfo')==2){
                $stuInfo=Db::name('stu_view')->where('s_id',request()->param('stuId'))
                    ->where($this->exchgFst[request()->param('authInfo')],request()->param('collegeid'))->find();
            }else {
                $stuInfo=Db::name('stu_view')->where('s_id',request()->param('stuId'))->find();
            }
        }catch (Exception $e){
            return json(array('msg'=>$e,'title'=>'输入有误请重试'));
            exit();
        }
        return json(array('msg'=>$stuInfo,'title'=>'查询成功'));
    }

    //小程序学分查询模块--------------------------------》结束
}