<?php
namespace i2conv\models\info21c;

use i2conv\Module;

class V3BidKey extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_key';
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
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->constnm) $this->constnm=iconv('utf-8','cp949',$this->constnm);
      if($this->org) $this->org=iconv('utf-8','cp949',$this->org);
      return true;
    }
    return false;
  }

  public function beforeDelete(){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->notinum) $this->notinum=iconv('cp949','utf-8',$this->notinum);
    if($this->constnm) $this->constnm=iconv('cp949','utf-8',$this->constnm);
    if($this->org) $this->org=iconv('cp949','utf-8',$this->org);
  }

  public function rules(){
    return [
      [['orgcode','org','contract'],'default','value'=>''],
      [['whereis','bidtype','con','ser','pur','notinum','orgcode','constnm',],'safe'],
      [['bidproc','bidcls','succls','conlevel','ulevel'],'safe'],
      [['concode','sercode','purcode','location','convention'],'safe'],
      [['presum','basic','pct','registdate','explaindate','agreedate','opendate'],'safe'],
      [['closedate','constdate','writedate','reswdate','state','in_id'],'safe'],
    ];
  }

  public function getV3BidValue(){
    return $this->hasOne(V3BidValue::className(),['bidid'=>'bidid']);
  }

  public function getV3BidContent(){
    return $this->hasOne(V3BidContent::className(),['bidid'=>'bidid']);
  }

  public function getV3BidResult(){
    return $this->hasOne(V3BidResult::className(),['bidid'=>'bidid']);
  }

  public function getV3BidLocals(){
    return $this->hasMany(V3BidLocal::className(),['bidid'=>'bidid']);
  }

  public function getV3BidGoods(){
    return $this->hasMany(V3BidGoods::className(),['bidid'=>'bidid']);
  }
}

