<?php
namespace i2conv\models\info21c;

class DpaSucCom extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'DPA_SucCom';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['id','bidtype'],'required'],
      [['officecode','seq','officeno','officename','pre_name','success','pct','result'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officename) $this->officename=iconv('cp949','utf-8',$this->officename);
    if($this->pre_name)   $this->pre_name=  iconv('cp949','utf-8',$this->pre_name);
    if($this->result)     $this->result=    iconv('cp949','utf-8',$this->result);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->officename) $this->officename=iconv('utf-8','cp949',$this->officename);
      if($this->pre_name)   $this->pre_name=  iconv('utf-8','cp949',$this->pre_name);
      if($this->result)     $this->result=    iconv('utf-8','cp949',$this->result);
      return true;
    }
    return false;
  }
}

