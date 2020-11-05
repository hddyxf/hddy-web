<?php
namespace app\index\model;
use think\Model;
use traits\model\SoftDelete; 
class User extends Model
{
    protected $auto =  ['ip','password','datetime'];
    protected function setIpAttr()
    {
        return request()->ip();
    }
    protected function setTime()
    {
        $time = date('Y-m-d H:i:s');
        return $time;
    }
    protected function setpasswordAttr ($value)
    {
        return md5($value);
    }
}  