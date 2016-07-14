<?php
namespace i2conv\models\info21c;

class PurRes extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_res';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function primaryKey(){
    return ['id'];
  }

  public function rules(){
    return [
      [['id'],'required'],
      [['notinum','bunryuno','process','jeibchal_bunho','notice_result'],'safe'],
      [['rs_selected_num','rs_gechal_ilsi','rs_yega1','rs_yega2','rs_yega3'],'safe'],
      [['rs_yega4','rs_yega5','rs_yega6','rs_yega7','rs_yega8','rs_yega9'],'safe'],
      [['rs_yega10','rs_yega11','rs_yega12','rs_yega13','rs_yega14','rs_yega15'],'safe'],
      [['yega','gichoamt','rs_jungjung_contents'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->notinum) $this->notinum=iconv('cp949','utf-8',$this->notinum);
    if($this->notice_result) $this->notice_result=iconv('cp949','utf-8//IGNORE',$this->notice_result);
    if($this->rs_jungjung_contents) $this->rs_jungjung_contents=iconv('cp949','utf-8//IGNORE',$this->rs_jungjung_contents);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->notice_result) $this->notice_result=iconv('utf-8','cp949//IGNORE',$this->notice_result);
      if($this->rs_jungjung_contents) $this->rs_jungjung_contents=iconv('utf-8','cp949//IGNORE',$this->rs_jungjung_contents);
      return true;
    }
    return false;
  }
}

