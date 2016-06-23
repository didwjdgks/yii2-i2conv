<?php
namespace i2conv\models\i2;

class BidSubcode extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'bid_subcode';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->i2db;
  }

  public function beforeSave($insert){
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->g2b_code_nm) $this->g2b_code_nm=iconv('euckr','utf-8',$this->g2b_code_nm);

    $codeItem=CodeItem::findOne($this->i2_code);
    if($codeItem!==null)
      $this->_itemcode=$codeItem->itemcode;
  }

  private $_itemcode='';
  public function getItemcode(){
    return $this->_itemcode;
  }
}

