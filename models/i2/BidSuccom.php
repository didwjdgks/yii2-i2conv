<?php
namespace i2conv\models\i2;

use i2conv\Module;

class BidSuccom extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_succom';
  }

  public static function getDb(){
    return Module::getInstance()->i2db;
  }

  public static function primaryKey(){
    return ['bidid','seq'];
  }

  public function beforeSave($insert){
    return false;
  }

  public function beforeDelete(){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officenm) $this->officenm=iconv('euckr','utf-8',$this->officenm);
    if($this->prenm) $this->prenm=iconv('euckr','utf-8',$this->prenm);
  }
}

