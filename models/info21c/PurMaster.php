<?php
namespace i2conv\models\info21c;

class PurMaster extends \yii\db\ActiveRecord
{
  public static function tableName(){
    return 'pur_master';
  }

  public static function getDb(){
    return \i2conv\Module::getInstance()->infodb;
  }

  public function beforeSave($insert){
    if(parent::beforeSave($insert)){
      if($this->notinum) $this->notinum=iconv('utf-8','cp949',$this->notinum);
      if($this->constname) $this->constname=iconv('utf-8','cp949',$this->constname);
      if($this->org) $this->org=iconv('utf-8','cp949',$this->org);
      if($this->constract) $this->constract=iconv('utf-8','cp949',$this->constract);
      if($this->ibchalbangsik) $this->ibchalbangsik=iconv('utf-8','cp949',$this->ibchalbangsik);
      if($this->lockeyword) $this->lockeyword=iconv('utf-8','cp949',$this->lockeyword);
      if($this->commentoption) $this->commentoption=iconv('utf-8','cp949',$this->commentoption);
      if($this->successname) $this->successname=iconv('utf-8','cp949',$this->successname);
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
    if($this->constname) $this->constname=iconv('cp949','utf-8',$this->constname);
    if($this->org) $this->org=iconv('cp949','utf-8',$this->org);
    if($this->constract) $this->constract=iconv('cp949','utf-8',$this->constract);
    if($this->ibchalbangsik) $this->ibchalbangsik=iconv('cp949','utf-8',$this->ibchalbangsik);
    if($this->lockeyword) $this->lockeyword=iconv('cp949','utf-8',$this->lockeyword);
    if($this->commentoption) $this->commentoption=iconv('cp949','utf-8',$this->commentoption);
    if($this->successname) $this->successname=iconv('cp949','utf-8',$this->successname);
  }

  public function rules(){
    return [
      [['id','subseq'],'required'],
      [['notinum','constname','org','constract','ibchalbangsik'],'safe'],
      [['gesi_dt','explain_dt','ibchalgesi_dt','ibchalmagam_dt','ibchal_dt','chamgamagam_dt'],'safe'],
      [['gichogongge_dt','write_dt','presum','basic','pct','itemcode','loccode'],'safe'],
      [['lockeyword','flags','dataprocess','commentoption','isjeibchal','whereis','yegatype'],'safe'],
      [['successamt','successname','state','sucprocess','register','goods_cnt','islast','org_code'],'safe'],
    ];
  }

  public function getPurFileData(){
    return $this->hasOne(PurFileData::className(),['id'=>'id','subseq'=>'subseq']);
  }
}

