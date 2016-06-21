<?php
namespace i2conv\models\info21c;

class V3BidItemcode extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_itemcode';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['bidid','bidtype','code'],'required'],
      [['name'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->name) $this->name=iconv('cp949','utf-8',$this->name);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->name) $this->name=iconv('utf-8','cp949',$this->name);
      return true;
    }
    return false;
  }
}

