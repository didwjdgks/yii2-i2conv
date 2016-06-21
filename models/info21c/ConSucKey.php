<?php
namespace i2conv\models\info21c;

class ConSucKey extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'ConSucKey';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function findNew($id){
    $instance=static::findOne($id);
    if($instance===null){
      $instance=new ConSucKey(['id'=>$id]);
    }
    return $instance;
  }

  public function rules(){
    return [
      [['item_code','location','constname','organization','notinum'],'safe'],
      [['contract_sys','basic','presum','yega','success','success_name'],'safe'],
      [['constdate','writedate','registdate','ulevel','in_id','whereis'],'safe'],
      [['state','pct','par_basic','level_cnt','org_code'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->constname) $this->constname=iconv('cp949','utf-8',$this->constname);
    if($this->organization) $this->organization=iconv('cp949','utf-8',$this->organization);
    if($this->notinum) $this->notinum=iconv('cp949','utf-8',$this->notinum);
    if($this->contract_sys) $this->contract_sys=iconv('cp949','utf-8',$this->contract_sys);
    if($this->success_name) $this->success_name=iconv('cp949','utf-8',$this->success_name);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->constname) $this->constname=iconv('utf-8','cp949',$this->constname);
      if($this->organization) $this->organization=iconv('utf-8','cp949',$this->organization);
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->contract_sys) $this->contract_sys=iconv('utf-8','cp949',$this->contract_sys);
      if($this->success_name) $this->success_name=iconv('utf-8','cp949',$this->success_name);
      return true;
    }
    return false;
  }
}

