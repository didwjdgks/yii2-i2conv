<?php
namespace i2conv\models\info21c;

class V3BidItemcode extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'v3_bid_itemcode';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function findNew($bidid,$bidtype,$code){
    $obj=static::findOne([
      'bidid'=>$bidid,
      'bidtype'=>$bidtype,
      'code'=>$code,
    ]);
    if($obj===null){
      $obj=\Yii::createObject([
        'class'=>static::className(),
        'bidid'=>$bidid,
        'bidtype'=>$bidtype,
        'code'=>$code,
      ]);
    }
    return $obj;
  }

  public function rules(){
    return [
      [['bidid','bidtype','code'],'required'],
      [['name'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->name) $this->name=iconv('cp949','utf-8',$this->name);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->name) $this->name=iconv('utf-8','cp949',$this->name);
      return true;
    }
    return false;
  }
}

