<?php
namespace i2conv\models\info21c;

class V3BidSubcode extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_subcode';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['bidid','g2b_code'],'required'],
      [['g2b_code_nm','i2_code','itemcode','pri_cont','share'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->g2b_code_nm) $this->g2b_code_nm=iconv('cp949','utf-8',$this->g2b_code_nm);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->g2b_code_nm) $this->g2b_code_nm=iconv('utf-8','cp949',$this->g2b_code_nm);
      return true;
    }
    return false;
  }
}

