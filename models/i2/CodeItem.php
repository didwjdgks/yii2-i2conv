<?php
namespace i2conv\models\i2;

class CodeItem extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'code_item';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->i2db;
  }

  public function beforeSave($insert){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->i2_name) $this->i2_name=iconv('euckr','utf-8',$this->i2_name);
  }
}

