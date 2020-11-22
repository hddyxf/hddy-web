<?php


namespace app\index\controller;


use think\Controller;
use think\Db;

class User extends Controller
{
    public function checkWx($wx)
    {
        $res = Db::name('user')->where('vx',$wx)->select();
        if(empty($res)) {
            return 1;
        }else{
            return 0;
        }
    }
}