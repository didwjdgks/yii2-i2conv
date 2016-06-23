<?php
namespace i2conv\models\i2;

class BidLocal extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_local';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->i2db;
  }

  public function beforeSave($insert){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->name) $this->name=iconv('euckr','utf-8',$this->name);
  }
}

