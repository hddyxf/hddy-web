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
    //请求PHP方法的参数
    private $data;
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
        $this->data=request()->param();
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


    //小程序学分审核模块变量区域--------------------------------》结束



    //小程序学分审核模块变量区域--------------------------------》结束


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


    //小程序学分审核操作主方法--------------------------------》开始



    public function ExamineRun(){
        //先根据ID取出学分操作日志中对应的记录
        $Record=Db::name('scoreoperation')->where('id',$this->data['id'])->find();
        //根据审核操作的类型去分别处理
        switch ($this->data['OperClass']){
            case 1://通过
                $this->EamScOper($Record);
                Db::name('scoreoperation')->where('id',$this->data['id'])->update(['opstate'=>6]);
                break;
            case 2://驳回
                Db::name('scoreoperation')->where('id',$this->data['id'])->update(['opstate'=>5]);
        }
        //如果通过，则1.进行学分操作补充 2.将审核状态更改为已确认
        //如果驳回 则1.将学分操作日志中待审核状态更改为已驳回
    }


    //小程序学分审核操作主方法--------------------------------》结束


    //小程序学分审核操作附属学分操作--------------------------------》开始


    public function EamScOper($Record){//传参传回的是scoreoperation表中的数据
    //根据数据中的操作分数和操作类型以及操作对象进行操作学分
     $this->scoreoper($Record['stuid'],$Record['score'],$Record['opscoreclass']);
     if ($this){
         return true;
     }else{
         return false;
     }
    }


    //小程序学分审核操作附属学分操作--------------------------------》结束


    //小程序学分审核模块--------------------------------》结束


    //小程序学分操作模块--------------------------------》开始


    //学分操作区域----------------------------------》开始

    public function scoreOperRange($opscoresec, $score)//判断操作分数是否超过限制
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
        return $this->ReturnMsg();
    }

    private $exchg = [
        '加分' => true,
        '减分' => false
    ];
    private $ls_exchg=[
        '加分'=>'1',
        '减分'=>'2'
    ];
    public function scoreoperationrun()//学分操作后台====================主方法
    {
        $date = input('post.');//获取变量
        $stuInfo = Db::name('students')
            ->where('s_id', $date['stuid'])
            ->find();//获取学生信息
            if ($this->scoreOperRange($date['opscoresec'], $date['score'])) {
                $ResScoreOperation = $this->scoreoper($date['stuid'], $date['score'], $date['opscoreclass']);
            }elseif (!$this->scoreOperRange($date['opscoresec'], $date['score'])){
                echo "<script type='text/javascript'>parent.layer.alert('学分操作超出限制');self.location=document.referrer;;;;</script>";
                exit();
            }
            $data['opscoreclass'] = $this->ls_exchg[$date['opscoreclass']];
            $RecordScoreOperation = Db::table('scoreoperation')->insert($data);//留存学分操作日志
            if ($RecordScoreOperation) {
                return json($this->ReturnMsg(true,$RecordScoreOperation,null));
            } else {
                return json($this->ReturnMsg(false,$RecordScoreOperation,null));            }
    }
    //学分操作区域----------------------------------》结束



    //小程序学分操作模块--------------------------------》结束
    //小程序查看学分操作日志模块--------------------------------》开始


    //小程序学分操作日志列表模块--------------------------------》开始


    public function LogList(){
        return Db::name('score_view')->limit(10)->find();
    }


    //小程序学分操作日志列表模块--------------------------------》开始


    //小程序查看学分操作日志模块主方法--------------------------------》开始


    public function checkLog(){
        return Db::name('score_view')->where('id',$this->data['id'])->find();
    }


    //小程序查看学分操作日志模块主方法--------------------------------》结束


    //小程序查看学分操作日志模块--------------------------------》结束


    //小程序提交意见反馈模块--------------------------------》开始


    public function subAdvice(){
        Db::name('advice')->insert($this->data);
    }


    //小程序提交意见反馈模块--------------------------------》结束




}