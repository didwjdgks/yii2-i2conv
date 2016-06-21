<?php
namespace i2conv\models\info21c;

class PurG2bGoods extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_g2b_goods';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['notinum','bunryu_no','sunbun_no','info_code','g2b_code','g2b_myung'],'safe'],
    ];
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->g2b_myung) $this->g2b_myung=iconv('utf-8','cp949',$this->g2b_myung);
      return true;
    }
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->notinum) $this->notinum=iconv('cp949','utf-8',$this->notinum);
    if($this->g2b_myung) $this->g2b_myung=iconv('cp949','utf-8',$this->g2b_myung);
  }
}

