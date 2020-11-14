<?php


namespace app\index\model;


class Users extends \think\Model
{
    protected $connection = [
        'type'=>'mysql',
        'hostname'=>'127.0.0.1',
        'database'=>'dysystem',
        'username'=>'root',
        'password'=>'123456',
        'charset'=>'utf8',
//        'prefix'=>'think_',
        'debug'=>true,
    ];
    protected	$table	=	'user';
}