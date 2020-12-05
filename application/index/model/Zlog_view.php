<?php


namespace app\index\model;


class Zlog_view extends \think\Model
{
    protected function scopeAll($query,$username,$start,$limit){
        $query->where('username',$username)->limit($start,$limit);
    }
}