<?php
namespace i2conv\models\i2;

use yii\db\Query;

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

  public function getValue(){
    return $this->hasOne(BidValue::className(),['bidid'=>'bidid']);
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
      'concode'=>$this->toV3BidKey_concode(),
      'sercode'=>$this->toV3BidKey_sercode(),
      'purcode'=>$this->toV3BidKey_purcode(),
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

  public function toV3BidKey_concode(){
    $concode=0;
    if(empty($this->concode) or strpos($this->concode,'C')===false){
      return 0;
    }
    $i2_codes=explode('|',$this->concode);
    $rows=(new Query())->from('code_item_i')
      ->innerJoin('code_item','code_item.i_code=code_item_i.code')
      ->select('code_item_i.itemcode')
      ->where([
          'code_item.i2_code'=>$i2_codes,
        ])
      ->all(static::getDb());
    foreach($rows as $row){
      if(($concode&pow(2,$row['itemcode']))==0) $concode+=pow(2,$row['itemcode']);
      switch($row['itemcode']){
      case 40:
      case 41:
      case 42:
      case 43:
        if(($concode&pow(2,7))==0) $concode+=pow(2,7);
        break;
      }
    }
    return $concode;
  }

  public function toV3BidKey_sercode(){
    $sercode=0;
    if(empty($this->sercode) or strpos($this->sercode,'S')===false){
      return 0;
    }
    $i2_codes=explode('|',$this->sercode);
    $rows=(new Query())->from('code_item_i')
      ->innerJoin('code_item','code_item.i_code=code_item_i.code')
      ->select('code_item_i.itemcode')
      ->where([
          'code_item.i2_code'=>$i2_codes,
        ])
      ->all(static::getDb());
    foreach($rows as $row){
      if(($sercode&pow(2,$row['itemcode']))==0) $sercode+=pow(2,$row['itemcode']);
      switch($row['itemcode']){
      case 32:
      case 33:
      case 34:
        if(($sercode&pow(2,3))==0) $sercode+=pow(2,3);
        break;
      case 54:
        if(($sercode&pow(2,9))==0) $sercode+=pow(2,9);
        break;
      case 62:
        if(($sercode&pow(2,22))==0) $sercode+=pow(2,22);
        break;
      }
    }
    return $sercode;
  }

  public function toV3BidKey_purcode(){
    $purcode=0;
    if(empty($this->purcode) or strpos($this->purcode,'P')===false) return 0;
    
    $i2_codes=explode('|',$this->purcode);
    $rows=(new Query())->from('code_item_i')
      ->innerJoin('code_item','code_item.i_code=code_item_i.code')
      ->select('code_item_i.itemcode')
      ->where(['code_item.i2_code'=>$i2_codes])
      ->all(static::getDb());
    foreach($rows as $row){
      if(($purcode&pow(2,$row['itemcode']))==0) $purcode+=pow(2,$row['itemcode']);
    }
    return $purcode;
  }

  public function toV3BidItemcodes_attributes(){
    $i2_codes=[];
    if(!empty($this->concode) and strpos($this->concode,'C')===0){
      $i2_codes=array_merge($i2_codes,explode('|',$this->concode));
    }
    if(!empty($this->sercode) and strpos($this->sercode,'S')===0){
      $i2_codes=array_merge($i2_codes,explode('|',$this->sercode));
    }
    if(!empty($this->purcode) and strpos($this->purcode,'P')===0){
      $i2_codes=array_merge($i2_codes,explode('|',$this->purcode));
    }
    $rows=(new Query())->from('code_item_i')
      ->innerJoin('code_item','code_item.i_code=code_item_i.code')
      ->select('code_item_i.code,code_item_i.name,code_item_i.bidtype')
      ->where(['code_item.i2_code'=>$i2_codes])
      ->all(static::getDb());
    $attrs=[];
    foreach($rows as $row){
      $attrs[]=[
        'bidtype'=>$row['bidtype'],
        'code'=>$row['code'],
        'name'=>iconv('euckr','utf-8',$row['name']),
      ];
    }

    return $attrs;
  }
}

