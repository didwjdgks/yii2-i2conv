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
    if($this->constno) $this->constno=iconv('euckr','utf-8',$this->constno);
    if($this->refno) $this->refno=iconv('euckr','utf-8',$this->refno);
    if($this->realorg) $this->realorg=iconv('euckr','utf-8',$this->realorg);
    if($this->charger) $this->charger=iconv('euckr','utf-8',$this->charger);
    if($this->promise_org) $this->promise_org=iconv('euckr','utf-8',$this->promise_org);
  }

  public function toV3BidValueAttributes(){
    return [
      'scrcls'=>$this->scrcls,
      'scrid'=>$this->scrid,
      'constno'=>$this->constno,
      'refno'=>$this->refno,
      'realorg'=>$this->realorg,
      'yegatype'=>$this->yegatype,
      'yegarng'=>str_replace('|','/',$this->yegarng),
      'prevamt'=>$this->prevamt,
      'multispare'=>str_replace('|','/',$this->multispare),
      'parbasic'=>$this->parbasic,
      'lvcnt'=>$this->lvcnt,
      'contloc'=>$this->contloc,
      'contper'=>$this->contper,
      'charger'=>str_replace('|','/',$this->charger),
    ];
  }
}

