<?php


namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\Loader;
use think\Validate;

class Wechat2 extends Controller
{
    //小程序PHP变量区--------------------------------》开始

    //权限ID
    private $AuthID;
    //存放报错信息


    //小程序PHP变量区--------------------------------》结束



    //小程序PHP初始化赋值--------------------------------》开始


    protected function _initialize(){
        //对传入参数进行格式验证
        $ResValidate= Loader::validate('Wechat2');
        if (!$ResValidate->check(request()->param()))
        {
            echo $ResValidate->getError();
            exit();
        }
        //初始化赋值
        $this->AuthID=number_format(request()->param('AuthID')) ;
    }


    //小程序PHP初始化赋值--------------------------------》结束


    //小程序生成返回信息--------------------------------》开始


    public function ReturnMsg($status,$msg,$title){
        if (!$status){
            return array('title'=>$title==null?'操作失败':$title,'msg'=>$msg,'code'=>'550');
        }elseif ($status){
            return array('title'=>$title==null?'操作成功':$title,'msg'=>$msg,'code'=>'220');
        }
    }


    //小程序生成返回信息---------------------------------》结束


    //小程序学分查询模块--------------------------------》开始
    //替换权限为限制条件

    private $exchgFst=array('4'=>'collegeid','5'=>'collegeid','6'=>'teacherid','7'=>'class','10'=>'s_apartment');
    private $AllrgAuth=[1,2,3];
    private $PrtrgAuth=[4,5,6,7,8,9,10];

    public function selectStuInfo(){
        try {
                if (in_array($this->AuthID,$this->PrtrgAuth))//限制权限
                {
                    $StuInfo=Db::name('stu_view')
                        ->where('s_id',request()->param('stuId'))
                        ->where($this->exchgFst[request()->param('AuthID')],request()->param('LimtParam'))
                        ->find();
                }
                elseif(in_array($this->AuthID,$this->AllrgAuth))//全局权限
                {
                    $StuInfo=Db::name('stu_view')->where('s_id',request()->param('stuId'))->find();
                }
                elseif ($this->AuthID=='9')//特殊公寓中心权限
                {
                    $StuInfo=Db::name('stu_view')
                        ->where('s_id',request()->param('stuId'))
                        ->where('s_apartment','not null')
                        ->find();
                }
        }
        catch (Exception $e)
        {
            return json(array('msg'=>$e,'title'=>'输入有误请重试'));
        }
        return json($StuInfo==null?array('msg'=>$StuInfo,'title'=>'查询结果为空'):array('msg'=>$StuInfo,'title'=>'查询成功'));
    }


    //小程序学分查询模块--------------------------------》结束


    //小程序学分审核模块--------------------------------》开始
    //小程序学分审核列表--------------------------------》开始


    public function ExamineList(){
        try {
            if (in_array($this->AuthID,$this->PrtrgAuth))//限制权限
            {
                $ExamineList=Db::name('score_view')
                    ->where('opstate',2)
                    ->where($this->exchgFst[request()->param('AuthID')],request()->param('LimtParam'))
                    ->select();
            }
            elseif(in_array($this->AuthID,$this->AllrgAuth))//全局权限
            {
                $ExamineList=Db::name('score_view')->where('opstate',2)->select();
            }
            elseif ($this->AuthID=='9')//特殊公寓中心权限
            {
                $ExamineList=Db::name('stu_view')
                    ->where('opstate',2)
                    ->where('s_apartment','not null')
                    ->select();
            }
        }catch (Exception $e){
            return json(array('msg'=>$e,'title'=>'输入有误请重试'));
        }
        return json($ExamineList==null?array('msg'=>$ExamineList,'title'=>'查询结果为空'):array('msg'=>$ExamineList,'title'=>'查询成功'));
    }


    //小程序学分审核列表--------------------------------》结束


    //小程序学分审核操作--------------------------------》开始


    public function ExamineRun(){
        try
        {
            $scoreOperation=$this->scoreoperationrun(Db::name('score_view')->where('id',request()->param('id'))->find());
            if ($scoreOperation['code']=='550')
            {
                return json($scoreOperation);
            }
            elseif ($scoreOperation['code']=='220')
            {
                Db::name('scoreoperation')->where('id',request()->param('id'))->update([
                    'opstate'=>1
                ]);
                return json($scoreOperation);
            }
        }
        catch (Exception $e)
        {
            return json($this->ReturnMsg(false,$e,null));
        }
    }


    //小程序学分审核操作--------------------------------》结束


    //小程序学分审核模块--------------------------------》结束


    //小程序学分操作模块--------------------------------》开始


    //学分操作区域----------------------------------》开始

    public function scoreOperRange($opscoresec, $score)//判断操作分数
    {
        return number_format(Db::name("scoresec")->where('scoresecid', $opscoresec)->value('score')) >= $score;
    }

    //用于判断当前分数操作后总分是否超过上下限，以及完成加分
    public function scoreoper($stuid, $score, $opscoreclass)
    {
        //获取当前学生的分数
//        halt(array($stuid,$score,$opscoreclass));
        if ($this->exchg[$opscoreclass]) {//判断加分
            Db::name('students')->where('s_id', $stuid)->setInc('score',$score);//先加分
            //再判断界限
            if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) > 100) {
                //保持临界值
                Db::name('students')->where('s_id', $stuid)->update(['score' => '100']);
                echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最高100分');self.location=document.referrer;;</script>";
                exit();
            };
        } elseif (!$this->exchg[$opscoreclass]) {//判断减分
            Db::name('students')->where('s_id', $stuid)->setDec('score',$score);//先减分
            //再判断界限
            if (number_format(Db::name('students')->where('s_id', $stuid)->value('score')) < 0) {
                //保持临界值
                Db::name('students')->where('s_id', $stuid)->update(['score' => '0']);
                echo "<script type='text/javascript'>parent.layer.alert('操作成功但德育学分最低0分');self.location=document.referrer;;</script>";
                exit();
            }
        }
    }

    private $exchg = [
        '加分' => true,
        '减分' => false
    ];
    private $ls_exchg=[
        '加分'=>'1',
        '减分'=>'2'
    ];
    public function scoreoperationrun($ExaminInfo)//学分操作后台
    {
        $date = input('post.');//获取变量
        $score = Db::name('students')
            ->where('s_id', $date['stuid'])
            ->find();//获取学生信息
        $score1 = number_format($score['score']);
            if ($this->scoreOperRange($date['opscoresec'], $date['score'])) {
                $scoreopartion = $this->scoreoper($date['stuid'], $date['score'], $date['opscoreclass']);
            }elseif (!$this->scoreOperRange($date['opscoresec'], $date['score'])){
                echo "<script type='text/javascript'>parent.layer.alert('学分操作超出限制');self.location=document.referrer;;;;</script>";
                exit();
            }
            $data['opscoreclass'] = $this->ls_exchg[$date['opscoreclass']];
            $scoreopartion = Db::table('scoreoperation')->insert($data);
            if ($scoreopartion) {
                return json($this->ReturnMsg(true,$scoreopartion,null));
            } else {
                return json($this->ReturnMsg(false,$scoreopartion,null));            }
    }
    //学分操作区域----------------------------------》结束



    //小程序学分操作模块--------------------------------》结束


}