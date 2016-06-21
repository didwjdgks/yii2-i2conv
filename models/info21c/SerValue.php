<?php
namespace i2conv\models\info21c;

class SerValue extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'SerValue';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public static function findNew($id){
    $instance=static::findOne($id);
    if($instance===null){
      $instance=new SerValue(['id'=>$id]);
    }
    return $instance;
  }

  public function rules(){
    return [
      [['parent','sun','multispare1','multispare2','multispare3','multispare4'],'safe'],
      [['multispare5','multispare6','multispare7','multispare8','multispare9'],'safe'],
      [['multispare10','multispare11','multispare12','multispare13','multispare14'],'safe'],
      [['multispare15','hyup_enddate','selms','original_link'],'safe'],
      [['filename1','filename2','filename3','par_basic','level_cnt','attatched_file_lnk'],'safe'],
    ];
  }

  public function afterFind(){
    parent::afterFind();
    if($this->original_link) $this->original_link=iconv('cp949','utf-8',$this->original_link);
    if($this->filename1) $this->filename1=iconv('cp949','utf-8',$this->filename1);
    if($this->filename2) $this->filename2=iconv('cp949','utf-8',$this->filename2);
    if($this->filename3) $this->filename3=iconv('cp949','utf-8',$this->filename3);
    if($this->attatched_file_lnk) $this->attatched_file_lnk=iconv('cp949','utf-8',$this->attatched_file_lnk);
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->original_link) $this->original_link=iconv('utf-8','cp949',$this->original_link);
      if($this->filename1) $this->filename1=iconv('utf-8','cp949',$this->filename1);
      if($this->filename2) $this->filename2=iconv('utf-8','cp949',$this->filename2);
      if($this->filename3) $this->filename3=iconv('utf-8','cp949',$this->filename3);
      if($this->attatched_file_lnk) $this->attatched_file_lnk=iconv('utf-8','cp949',$this->attatched_file_lnk);
      return true;
    }
    return false;
  }
}

