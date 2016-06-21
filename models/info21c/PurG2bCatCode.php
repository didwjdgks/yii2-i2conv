<?php
namespace i2conv\models\info21c;

class PurG2bCatCode extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_g2b_cat_code';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function beforeSave($insert){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->g2bname) $this->g2bname=iconv('cp949','utf-8',$this->g2bname);
  }
}

