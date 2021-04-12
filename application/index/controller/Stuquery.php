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
      $date['term']=explode(',',$date['term']);
      if ($date['term'] !=null){
          $date['term'][0]=intval($date['term'][0]);
          $date['term'][1]=intval($date['term'][1]);
      }
      $validate = new validate([
          ['proid','require|max:18|[0-9]{17}[0-9xX]','身份证号码不能为空|身份证号码限制不能超过18位|身份证号码不规范！'],
          ['u_id','require|max:11|number','学号不能为空|学号限制不能超过11位|学号限制为数字'],
          ['type','require','单学期查询开启选择不能为空'],
          ]);
      if (!$validate->check($date)){
         $msg = $validate->getError();
          echo  "<script>alert('$msg');self.location=document.referrer;</script>";
        //判断数据是否合法
      } else {
          //首先要组成时间区间
          $plus=4;
          $time_lmt1=$date['enrol']+$date['grade']+$date['term'][0].'-'.$date['term'][1].'-'.'1';
          $time_lmt2=$date['enrol']+$date['grade']+$date['term'][0].'-'.strval($plus+$date['term'][1]).'-'.'30';
          //早区间测定
           $result1 = Db::table('stu_view')
                  ->where('s_id',$date['u_id'])
                  ->where('s_proid',$date['proid'])
                  ->find();
            if($result1){
                if ($date['type']=='1'){
                    $result2 = Db::table('score_view')
                        ->where('s_id', $date["u_id"])
                        ->whereTime('datetime','between',[$time_lmt1,$time_lmt2])
                        ->select();
                }elseif($date['type']=='2'){
                    $result2 = Db::table('score_view')
                        ->where('s_id', $date["u_id"])
                        ->select();
                }
        $this -> assign('data',$result2); 
        $this -> assign('date1',$result1);
       return $this->fetch(); 
            } else {
                $this->error("系统中不存在学号 {$date['u_id']} 与身份证号 {$date['proid']} 相匹配的的学生信息。");//数据为空返回错误
            }
          
      }
    }
}
