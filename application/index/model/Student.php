<?php


namespace app\index\model;


class Student extends \think\Model
{
    protected $connection = [
        'type'=>'mysql',
        'hostname'=>'127.0.0.1',
        'database'=>'dysystem',
        'username'=>'root',
        'password'=>'123456',
        'charset'=>'utf8',
        'debug'=>true,
    ];
    protected	$table	=	'stu_view';
    public function users(){
        return $this->hasOne('users');
    }
}