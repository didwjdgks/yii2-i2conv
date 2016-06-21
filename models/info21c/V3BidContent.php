<?php
namespace i2conv\models\info21c;

class V3BidContent extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_content';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function rules(){
    return [
      [['bidid'],'required'],
      [['important_bid','content_bid','important_suc','content_suc'],'safe'],
      [['upfile_bid','upfile_suc'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->important_bid) $this->important_bid=iconv('cp949','utf-8',$this->important_bid);
    if($this->content_bid)   $this->content_bid=  iconv('cp949','utf-8',$this->content_bid);
    if($this->important_suc) $this->important_suc=iconv('cp949','utf-8',$this->important_suc);
    if($this->content_suc)   $this->content_suc=  iconv('cp949','utf-8',$this->content_suc);
    if($this->upfile_bid)    $this->upfile_bid=   iconv('cp949','utf-8',$this->upfile_bid);
    if($this->upfile_suc)    $this->upfile_suc=   iconv('cp949','utf-8',$this->upfile_suc);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->important_bid) $this->important_bid=iconv('utf-8','cp949',$this->important_bid);
      if($this->content_bid)   $this->content_bid=  iconv('utf-8','cp949',$this->content_bid);
      if($this->important_suc) $this->important_suc=iconv('utf-8','cp949',$this->important_suc);
      if($this->content_suc)   $this->content_suc=  iconv('utf-8','cp949',$this->content_suc);
      if($this->upfile_bid)    $this->upfile_bid=   iconv('utf-8','cp949',$this->upfile_bid);
      if($this->upfile_suc)    $this->upfile_suc=   iconv('utf-8','cp949',$this->upfile_suc);
      return true;
    }
    return false;
  }
}

