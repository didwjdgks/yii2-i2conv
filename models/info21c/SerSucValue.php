<?php
namespace i2conv\models\info21c;

class SerSucValue extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'SerSucValue';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function findNew($id){
    $instance=static::findOne($id);
    if($instance===null){
      $instance=new SerSucValue(['id'=>$id]);
    }
    return $instance;
  }

  public function rules(){
    return [
      [['parent','sun','multispare1','multispare2','multispare3','multispare4'],'safe'],
      [['multispare5','multispare6','multispare7','multispare8','multispare9'],'safe'],
      [['multispare10','multispare11','multispare12','multispare13','multispare14'],'safe'],
      [['multispare15','selms','original_link'],'safe'],
      [['filename1','filename2','filename3'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->original_link) $this->original_link=iconv('cp949','utf-8',$this->original_link);
    if($this->filename1) $this->filename1=iconv('cp949','utf-8',$this->filename1);
    if($this->filename2) $this->filename2=iconv('cp949','utf-8',$this->filename2);
    if($this->filename3) $this->filename3=iconv('cp949','utf-8',$this->filename3);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->original_link) $this->original_link=iconv('utf-8','cp949',$this->original_link);
      if($this->filename1) $this->filename1=iconv('utf-8','cp949',$this->filename1);
      if($this->filename2) $this->filename2=iconv('utf-8','cp949',$this->filename2);
      if($this->filename3) $this->filename3=iconv('utf-8','cp949',$this->filename3);
      return true;
    }
    return false;
  }
}

