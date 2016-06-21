<?php
namespace i2conv\models\info21c;

class KepcoSucCom extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'KEPCO_SucCom';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['id','bidtype','seq','pre_name'],'required'],
      [['officeno','officename','rank','success','pct','ext1','ext2'],'safe'],
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

