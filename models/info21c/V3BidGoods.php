<?php
namespace i2conv\models\info21c;

use i2conv\Module;

class V3BidGoods extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_goods';
  }

  public static function getDb(){
    return Module::getInstance()->infodb;
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->gname)     $this->gname=    iconv('utf-8','cp949',$this->gname);
      if($this->gorg)      $this->gorg=     iconv('utf-8','cp949',$this->gorg);
      if($this->standard)  $this->standard= iconv('utf-8','cp949',$this->standard);
      if($this->unit)      $this->unit=     iconv('utf-8','cp949',$this->unit);
      if($this->unitcost)  $this->unitcost= iconv('utf-8','cp949',$this->unitcost);
      if($this->period)    $this->period=   iconv('utf-8','cp949',$this->period);
      if($this->place)     $this->place=    iconv('utf-8','cp949',$this->place);
      if($this->condition) $this->condition=iconv('utf-8','cp949',$this->condition);
      return true;
    }
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->gname)     $this->gname=    iconv('cp949','utf-8',$this->gname);
    if($this->gorg)      $this->gorg=     iconv('cp949','utf-8',$this->gorg);
    if($this->standard)  $this->standard= iconv('cp949','utf-8',$this->standard);
    if($this->unit)      $this->unit=     iconv('cp949','utf-8',$this->unit);
    if($this->unitcost)  $this->unitcost= iconv('cp949','utf-8',$this->unitcost);
    if($this->period)    $this->period=   iconv('cp949','utf-8',$this->period);
    if($this->place)     $this->place=    iconv('cp949','utf-8',$this->place);
    if($this->condition) $this->condition=iconv('cp949','utf-8',$this->condition);
  }

  public function rules(){
    return [
      [['bidid','seq','gcode','gname'],'required'],
      [['gorg','standard','cnt','unit'],'safe'],
      [['unitcost','period','place','condition'],'safe'],
    ];
  }
}

