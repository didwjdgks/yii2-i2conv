<?php
namespace i2conv\models\info21c;

class KrSucCom extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'KR_SucCom';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['id','officeno'],'required'],
      [['officename','pre_name','rank','success','pct'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officename) $this->officename=iconv('cp949','utf-8',$this->officename);
    if($this->pre_name) $this->pre_name=iconv('cp949','utf-8',$this->pre_name);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->officename) $this->officename=iconv('utf-8','cp949',$this->officename);
      if($this->pre_name) $this->pre_name=iconv('utf-8','cp949',$this->pre_name);
      return true;
    }
    return false;
  }
}

