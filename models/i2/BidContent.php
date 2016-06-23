<?php
namespace i2conv\models\i2;

class BidContent extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_content';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->i2db;
  }

  public function beforeSave($insert){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->bidcomment_mod) $this->bidcomment_mod=iconv('euckr','utf-8//IGNORE',$this->bidcomment_mod);
    if($this->bidcomment)     $this->bidcomment=    iconv('euckr','utf-8//IGNORE',$this->bidcomment);
    if($this->bid_html)       $this->bid_html=      iconv('euckr','utf-8//IGNORE',$this->bid_html);
    if($this->nbidcomment)    $this->nbidcomment=   iconv('euckr','utf-8//IGNORE',$this->nbidcomment);
    if($this->nbid_html)      $this->nbid_html=     iconv('euckr','utf-8//IGNORE',$this->nbid_html);
    if($this->bid_file)       $this->bid_file=      iconv('euckr','utf-8//IGNORE',$this->bid_file);
    if($this->nbid_file)      $this->nbid_file=     iconv('euckr','utf-8//IGNORE',$this->nbid_file);
  }
}

