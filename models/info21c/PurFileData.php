<?php
namespace i2conv\models\info21c;

class PurFileData extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_file_data';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->linkdata) $this->linkdata=iconv('utf-8','cp949//IGNORE',$this->linkdata);
      if($this->linkdata2) $this->linkdata2=iconv('utf-8','cp949//IGNORE',$this->linkdata2);
      if($this->filedata1) $this->filedata1=iconv('utf-8','cp949//IGNORE',$this->filedata1);
      if($this->jungjungdata) $this->jungjungdata=iconv('utf-8','cp949//IGNORE',$this->jungjungdata);
      if($this->maincontents) $this->maincontents=iconv('utf-8','cp949//IGNORE',$this->maincontents);
      if($this->urlinfo1) $this->urlinfo1=iconv('utf-8','cp949//IGNORE',$this->urlinfo1);
      if($this->urlinfo2) $this->urlinfo2=iconv('utf-8','cp949//IGNORE',$this->urlinfo2);
      if($this->openbid_contents) $this->openbid_contents=iconv('utf-8','cp949//IGNORE',$this->openbid_contents);
      return true;
    }
    return false;
  }

  public function afterFind(){
    parent::afterFind();
    if($this->linkdata) $this->linkdata=iconv('cp949','utf-8//IGNORE',$this->linkdata);
    if($this->linkdata2) $this->linkdata2=iconv('cp949','utf-8//IGNORE',$this->linkdata2);
    if($this->filedata1) $this->filedata1=iconv('cp949','utf-8//IGNORE',$this->filedata1);
    if($this->jungjungdata) $this->jungjungdata=iconv('cp949','utf-8//IGNORE',$this->jungjungdata);
    if($this->maincontents) $this->maincontents=iconv('cp949','utf-8//IGNORE',$this->maincontents);
    if($this->urlinfo1) $this->urlinfo1=iconv('cp949','utf-8//IGNORE',$this->urlinfo1);
    if($this->urlinfo2) $this->urlinfo2=iconv('cp949','utf-8//IGNORE',$this->urlinfo2);
    if($this->openbid_contents) $this->openbid_contents=iconv('cp949','utf-8//IGNORE',$this->openbid_contents);
  }

  public function rules(){
    return [
      [['id','subseq'],'required'],
      [['linkdata','linkdata2','filedata1','jungjungdata'],'safe'],
      [['maincontents','urlinfo1','urlinfo2','openbid_contents'],'safe'],
    ];
  }
}

