<?php
namespace i2conv\models\info21c;

use i2conv\Module;

class V3BidResult extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_result';
  }

  public static function getDb(){
    return Module::getInstance()->infodb;
  }

  public static function findNew($bidid){
    $obj=static::findOne($bidid);
    if($obj===null){
      $obj=\Yii::createObject([
        'class'=>static::className(),
        'bidid'=>$bidid,
      ]);
    }
    return $obj;
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->officenm1) $this->officenm1=iconv('utf-8','cp949',$this->officenm1);
      if($this->prenm1) $this->prenm1=iconv('utf-8','cp949',$this->prenm1);
      return true;
    }else{
      return false;
    }
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officenm1) $this->officenm1=iconv('cp949','utf-8',$this->officenm1);
    if($this->prenm1) $this->prenm1=iconv('cp949','utf-8',$this->prenm1);
  }

  public function rules(){
    return [
      [['yega','selms','innum','officenm1','prenm1','officeno1'],'safe'],
      [['success1','sucid','resdt','reswdt'],'safe'],
    ];
  }
}

