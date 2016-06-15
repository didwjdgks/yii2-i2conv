<?php
namespace i2conv\models\i2;

use i2conv\Module;

class BidGoods extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_goods';
  }

  public static function getDb(){
    return Module::getInstance()->i2db;
  }

  public function beforeSave($insert){
    return false;
  }
  public function beforeDelete(){
    return false;
  }
  public function afterFind(){
    parent::afterFind();
    if($this->gname)     $this->gname=    iconv('euckr','utf-8',$this->gname);
    if($this->gorg)      $this->gorg=     iconv('euckr','utf-8',$this->gorg);
    if($this->standard)  $this->standard= iconv('euckr','utf-8',$this->standard);
    if($this->unit)      $this->unit=     iconv('euckr','utf-8',$this->unit);
    if($this->unitcost)  $this->unitcost= iconv('euckr','utf-8',$this->unitcost);
    if($this->period)    $this->period=   iconv('euckr','utf-8',$this->period);
    if($this->place)     $this->place=    iconv('euckr','utf-8',$this->place);
    if($this->condition) $this->condition=iconv('euckr','utf-8',$this->condition);
  }

  public function toV3BidGoodsAttributes(){
    return $this->attributes;
  }
}

