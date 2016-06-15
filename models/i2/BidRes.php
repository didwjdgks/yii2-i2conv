<?php
namespace i2conv\models\i2;

use i2conv\Module;

class BidRes extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_res';
  }

  public static function getDb(){
    return Module::getInstance()->i2db;
  }

  public function beforeSave(){
    return false;
  }

  public function beforeDelete(){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->officenm1) $this->officenm1=iconv('euckr','utf-8',$this->officenm1);
    if($this->prenm1) $this->prenm1=iconv('euckr','utf-8',$this->prenm1);
  }

  public function getReswdtText(){
    return date('Y/m/d H:i:s',$this->reswdt);
  }

  public function toV3BidResultAttributes(){
    $reswdt=(strtotime($this->reswdt)>0)?strtotime($this->reswdt):0;
    return [
      'yega'=>$this->yega,
      'selms'=>str_replace('|','-',$this->selms),
      'innum'=>$this->innum,
      'officenm1'=>$this->officenm1,
      'prenm1'=>$this->prenm1,
      'officeno1'=>$this->officeno1,
      'success1'=>$this->success1,
      'reswdt'=>$reswdt,
    ];
  }

  public function toV3BidValueAttributes(){
    return [
      'multispare'=>str_replace('|','/',$this->multispare),
    ];
  }
}

