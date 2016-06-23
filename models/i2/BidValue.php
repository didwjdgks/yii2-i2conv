<?php
namespace i2conv\models\i2;

use i2conv\Module;

class BidValue extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_value';
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
    if($this->constno)      $this->constno    =iconv('euckr','utf-8',$this->constno);
    if($this->refno)        $this->refno      =iconv('euckr','utf-8',$this->refno);
    if($this->realorg)      $this->realorg    =iconv('euckr','utf-8',$this->realorg);
    if($this->charger)      $this->charger    =iconv('euckr','utf-8',$this->charger);
    if($this->promise_org)  $this->promise_org=iconv('euckr','utf-8',$this->promise_org);
    if($this->contloc)      $this->contloc    =iconv('euckr','utf-8',$this->contloc);
  }

}

