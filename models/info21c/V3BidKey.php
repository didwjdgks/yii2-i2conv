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

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
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
      [['whereis','bidtype','con','ser','pur','notinum','orgcode','constnm','org'],'safe'],
      [['bidproc','contract','bidcls','succls','conlevel','ulevel'],'safe'],
      [['concode','sercode','purcode','location','convention'],'safe'],
      [['presum','basic','pct','registdate','explaindate','agreedate','opendate'],'safe'],
      [['closedate','constdate','writedate','reswdate','state','in_id'],'safe'],
    ];
  }
}

