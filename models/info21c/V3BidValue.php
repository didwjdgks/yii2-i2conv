<?php
namespace i2conv\models\info21c;

use i2conv\Module;

class V3BidValue extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_value';
  }

  public static function getDb(){
    return Module::getInstance()->infodb;
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->constno) $this->constno=iconv('utf-8','cp949',$this->constno);
      if($this->refno) $this->refno=iconv('utf-8','cp949',$this->refno);
      if($this->realorg) $this->realorg=iconv('utf-8','cp949',$this->realorg);
      if($this->charger) $this->charger=iconv('utf-8','cp949',$this->charger);
      if($this->bidperm) $this->bidperm=iconv('utf-8','cp949',$this->bidperm);
      if($this->bidqorg) $this->bidqorg=iconv('utf-8','cp949',$this->bidqorg);
      if($this->origin_lnk) $this->origin_lnk=iconv('utf-8','cp949',$this->origin_lnk);
      if($this->attchd_lnk) $this->attchd_lnk=iconv('utf-8','cp949',$this->attchd_lnk);
      if($this->prem) $this->perm=iconv('utf-8','cp949',$this->perm);
      return true;
    }else{
      return false;
    }
  }

  public function afterFind(){
    parent::afterFind();
    if($this->constno) $this->constno=iconv('cp949','utf-8',$this->constno);
    if($this->refno) $this->refno=iconv('cp949','utf-8',$this->refno);
    if($this->realorg) $this->realorg=iconv('cp949','utf-8',$this->realorg);
    if($this->charger) $this->charger=iconv('cp949','utf-8',$this->charger);
    if($this->bidperm) $this->bidperm=iconv('cp949','utf-8',$this->bidperm);
    if($this->bidqorg) $this->bidqorg=iconv('cp949','utf-8',$this->bidqorg);
    if($this->origin_lnk) $this->origin_lnk=iconv('cp949','utf-8',$this->origin_lnk);
    if($this->attchd_lnk) $this->attchd_lnk=iconv('cp949','utf-8',$this->attchd_lnk);
    if($this->prem) $this->perm=iconv('cp949','utf-8',$this->perm);
  }

  public function rules(){
    return [
      [['scrcls','scrid','constno','refno','realorg','realorgcode','yegatype'],'safe'],
      [['yegarng','noticedt','registdt','explaindt','agreedt','opendt','closedt'],'safe'],
      [['constdt','writedt','editdt','prevamt','multispare','parbasic','lvcnt'],'safe'],
      [['keyid','contloc','contper','charger','bidqid','bidqorgcode','bidperm'],'safe'],
      [['origin_lnk','attchd_lnk','bidqorg','perm'],'safe'],
    ];
  }
}

