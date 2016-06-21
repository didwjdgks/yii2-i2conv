<?php
namespace i2conv\models\info21c;

class V3BidSuccom extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_succom';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['bidid','seq','constdate'],'required'],
      [['officeno','officenm','prenm','success','pct'],'safe'],
      [['regdt','rank','selms','etc'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officenm) $this->officenm=iconv('cp949','utf-8',$this->officenm);
    if($this->prenm) $this->prenm=iconv('cp949','utf-8',$this->prenm);
    if($this->etc) $this->etc=iconv('cp949','utf-8',$this->etc);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->officenm) $this->officenm=iconv('utf-8','cp949',$this->officenm);
      if($this->prenm) $this->prenm=iconv('utf-8','cp949',$this->prenm);
      if($this->etc) $this->etc=iconv('utf-8','cp949',$this->etc);
      return true;
    }
    return false;
  }
}

