<?php
namespace app\index\controller;
use think\Controller;
use think\validate;
use think\Db;
class Stuquery extends Controller
{
    
     
    public function stuquery()
    {
    	return $this->fetch();
    }  
    public function scorecheck()//学分查询
    {
      $date = input('post.');
      $validate = new validate([
          ['proid','require|max:18|[0-9]{17}[0-9xX]','身份证号码不能为空|身份证号码限制不能超过18位|身份证号码不规范！'],
          ['u_id','require|max:11|number','学号不能为空|学号限制不能超过11位|学号限制为数字'],]);
      if (!$validate->check($date)){
         $msg = $validate->getError();
          echo  "<script>alert('$msg');parent.history.go(-1)</script>";
        //判断数据是否合法
      } else {
           $result1 = Db::table('stu_view')
                  ->where('s_id',$date['u_id'])
                  ->where('s_proid',$date['proid'])
                  ->find();
            if($result1){
                $result2 = Db::table('score_view')
                ->where('s_id', $date["u_id"])
                ->select();
        $this -> assign('data',$result2); 
        $this -> assign('date1',$result1); 
       return $this->fetch(); 
            } else {
                $this->error("系统中不存在学号 {$date['u_id']} 与身份证号 {$date['proid']} 相匹配的的学生信息。");//数据为空返回错误
            }
          
      }
    }
}
