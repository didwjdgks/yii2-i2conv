<?php
namespace i2conv\models\i2;

use i2conv\Module;

class BidKey extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_key';
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
    if($this->notinum) $this->notinum=iconv('euckr','utf-8',$this->notinum);
    if($this->constnm) $this->constnm=iconv('euckr','utf-8',$this->constnm);
    if($this->org_i) $this->org_i=iconv('euckr','utf-8',$this->org_i);
  }

  public function getRes(){
    return $this->hasOne(BidRes::className(),['bidid'=>'bidid']);
  }

  public function getSuccoms(){
    return $this->hasMany(BidSuccom::className(),['bidid'=>'bidid']);
  }

  public function toV3BidKey_conlevel(){
    switch($this->conlevel){
      case '1': return 'A';
      case '2': return 'B';
      case '3': return 'C';
      case '4': return 'D';
      case '5': return 'E';
      case '6': return 'F';
      case '7': return 'G';
      case '8': return 'H';
      case '9': return 'I';
      case '10': return 'J';
      default: return '';
    }
  }

  public function toV3BidKeyAttributes(){
    return [
      'whereis'=>$this->whereis,
      'bidtype'=>$this->bidtype,
      'con'=>strpos($this->bidview,'con')===false?'N':'Y',
      'ser'=>strpos($this->bidview,'ser')===false?'N':'Y',
      'pur'=>strpos($this->bidview,'pur')===false?'N':'Y',
      'notinum'=>$this->notinum,
      'orgcode'=>$this->orgcode_i,
      'constnm'=>$this->constnm,
      'org'=>$this->org_i,
      'bidproc'=>$this->bidproc,
      'contract'=>$this->contract,
      'bidcls'=>$this->bidcls,
      'succls'=>$this->succls,
      'conlevel'=>$this->toV3BidKey_conlevel(),
      'ulevel'=>$this->opt,
      //concode=>$this->concode,
      //sercode=>
      //purcode=>
      'location'=>$this->location?$this->location:0,
      'convention'=>$this->convention=='3'?'2':$this->convention,
      'presum'=>$this->presum?$this->presum:0,
      'basic'=>$this->basic?$this->basic:0,
      'pct'=>$this->pct?$this->pct:'',
      'registdate'=>strtotime($this->registdt)>0?date('Y-m-d',strtotime($this->registdt)):'',
      'explaindate'=>strtotime($this->explaindt)>0?date('Y-m-d',strtotime($this->explaindt)):'',
      'agreedate'=>strtotime($this->agreedt)>0?date('Y-m-d',strtotime($this->agreedt)):'',
      'opendate'=>strtotime($this->opendt)>0?date('Y-m-d',strtotime($this->opendt)):'',
      'closedate'=>strtotime($this->closedt)>0?date('Y-m-d',strtotime($this->closedt)):'',
      'constdate'=>strtotime($this->constdt)>0?date('Y-m-d',strtotime($this->constdt)):'',
      'writedate'=>strtotime($this->writedt)>0?date('Y-m-d',strtotime($this->writedt)):'',
      'reswdate'=>strtotime($this->resdt)>0?date('Y-m-d',strtotime($this->resdt)):'',
      'state'=>$this->state,
      'in_id'=>91,
    ];
  }
}

