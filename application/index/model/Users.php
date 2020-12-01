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
        'debug'=>true,
    ];
    protected	$table	=	'user';
//    protected static function init($table)
//    {
////        parent::init();
//        $thistable=$table;
//    }

    public function set_table($table){
        $this->table=$table;
        return true;
//        $res=$this->get($data);
        return $res;
    }
    public function tes_table(){
        return $this->table;
    }
}