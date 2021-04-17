<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()//渲染登陆页为首页
    {
		$usrname=session('username');
		if(empty($usrname)){
		return $this->fetch();
		}else{
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">body,td,th{color: #FFFFFF;}body{background-color: #0099CC;}.STYLE7 {font-size: 24px;font-family: "微软雅黑";}.STYLE9 {font-size: 16px}.STYLE12 {font-size: 100px;font-family: "微软雅黑";}</style></head><body><script language="javascript" type="text/javascript">setTimeout(function () { top.location.href = "http://127.0.0.1:83" }, 5000);</script><span class="STYLE12">&nbsp;:(</span><p class="STYLE7">&nbsp&nbsp&nbsp&nbsp&nbsp检测到系统环境异常！系统将在5秒后正在自动跳转。<br>&nbsp&nbsp&nbsp&nbsp&nbsp您的操作已被中止，这可能是非法登陆或登陆超时导致，您可尝试重新登陆系统。<br/></body></html>';
            session_destroy();
            exit;
		}
    } 
    public function index1()//服务器需要维护时，将此方法名与上方方法名呼唤，修改下方提示语句和时间即首页变为维护页面。
    {
    	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>服务器维护</title>
<link rel="shortcut icon" href="logo.ico" />
<style type="text/css">
body {margin: 0px; padding:0px; font-family:"微软雅黑", Arial, "Trebuchet MS", Verdana, Georgia,Baskerville,Palatino,Times; font-size:16px;}
div{margin-left:auto; margin-right:auto;}
a {text-decoration: none; color: #1064A0;}
a:hover {color: #0078D2;}
img { border:none; }
h1,h2,h3,h4 {
/*	display:block;*/
	margin:0;
	font-weight:normal; 
	font-family: "微软雅黑", Arial, "Trebuchet MS", Helvetica, Verdana ; 
}
h1{font-size:44px; color:#0188DE; padding:20px 0px 10px 0px;}
h2{color:#0188DE; font-size:16px; padding:10px 0px 40px 0px;}

#page{width:910px; padding:20px 20px 40px 20px; margin-top:80px;}
.button{width:180px; height:28px; margin-left:0px; margin-top:10px; background:#009CFF; border-bottom:4px solid #0188DE; text-align:center;}
.button a{width:180px; height:28px; display:block; font-size:14px; color:#fff; }
.button a:hover{ background:#5BBFFF;}
</style>

</head>
<body>


<div id="page" style="border-style:dashed;border-color:#e4e4e4;line-height:30px;background:url(sorry.png) no-repeat right;">
	<h1>抱歉，服务器正在维护。</h1>
	<h2>Sorry, Server is being maintained. </h2>
	<font color="#666666">位了更好的给您提供服务，哈尔滨华德学院学生德育学分管理系统x相关服务将进行升级，我们将在最短的时间内，以最好的姿态回归，给您带来的不便，敬请谅解。</font><br /><br />
<div align="right">德育学分管理系统项目团队<br>2019年5月20日</div>
	</div>
</div>



</body>
</html>
';
    }     
 
}
