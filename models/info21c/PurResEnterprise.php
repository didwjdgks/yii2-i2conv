<?php
namespace i2conv\models\info21c;

class PurResEnterprise extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_res_enterprise';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function findNew($condition){
    $instance=static::findOne($condition);
    if($instance===null){
      $instance=new PurResEnterprise($condition);
    }
    return $instance;
  }

  public function rules(){
    return [
      [['id'],'required'],
      [['notinum','bunryuno','sunwi','saupja_bunho','upche_myung'],'safe'],
      [['depyoja','tuchal_gumek','tuchal_ryul','bigo'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->notinum) $this->notinum=iconv('cp949','utf-8',$this->notinum);
    if($this->upche_myung) $this->upche_myung=iconv('cp949','utf-8',$this->upche_myung);
    if($this->depyoja) $this->depyoja=iconv('cp949','utf-8',$this->depyoja);
    if($this->bigo) $this->bigo=iconv('cp949','utf-8',$this->bigo);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->upche_myung) $this->upche_myung=iconv('utf-8','cp949',$this->upche_myung);
      if($this->depyoja) $this->depyoja=iconv('utf-8','cp949',$this->depyoja);
      if($this->bigo) $this->bigo=iconv('utf-8','cp949',$this->bigo);
      return true;
    }
    return false;
  }
}

