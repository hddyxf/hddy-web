<?php
namespace app\index\controller;
use think\Controller;
class Help extends Controller
{
    public function help()
    {
    	return $this->fetch();
    }     
}
