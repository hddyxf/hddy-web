<?php


namespace app\index\model;


class Scorefirst extends \think\Model
{
 public function scoresec(){
  return $this->hasMany('Scoresec','scorefirid','scoreid');
 }
}