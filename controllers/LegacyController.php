<?php
namespace i2conv\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\db\Query;

use GearmanWorker;

use i2conv\models\info21c\V3BidKey;
use i2conv\models\info21c\V3BidGoods;
use i2conv\models\info21c\V3BidSuccom;

use i2conv\models\info21c\ConKey;
use i2conv\models\info21c\ConValue;
use i2conv\models\info21c\ConSucKey;
use i2conv\models\info21c\ConSucValue;
use i2conv\models\info21c\ConSucCom;
use i2conv\models\info21c\SerKey;
use i2conv\models\info21c\SerValue;
use i2conv\models\info21c\SerSucKey;
use i2conv\models\info21c\SerSucValue;
use i2conv\models\info21c\SerSucCom;
use i2conv\models\info21c\DpaSucCom;
use i2conv\models\info21c\KepcoSucCom;
use i2conv\models\info21c\KrSucCom;
use i2conv\models\info21c\JuSucCom;
use i2conv\models\info21c\PurMaster;
use i2conv\models\info21c\PurRes;
use i2conv\models\info21c\PurG2bGoods;
use i2conv\models\info21c\PurFileData;
use i2conv\models\info21c\PurResEnterprise;

class LegacyController extends \yii\console\Controller
{
  public function actionIndex(){
    $worker=new GearmanWorker;
    $worker->addServers($this->module->gman_server);
    $worker->addFunction('i2conv_legacy',[$this,'i2conv_legacy']);
    while($worker->work());
  }

  public function i2conv_legacy($job){
    $workload=$job->workload();
    $workload=Json::decode($workload);

    try {
      $this->module->i2db->close();
      $this->module->infodb->close();

      $v3bidkey=V3BidKey::findOne($workload['bidid']);
      if($v3bidkey===null) return;
      $v3bidvalue=$v3bidkey->v3BidValue;
      if($v3bidvalue===null) return;

      switch($v3bidkey->bidtype){
      case 'con': $this->stdout('[공사]',Console::FG_YELLOW); break;
      case 'ser': $this->stdout('[용역]',Console::FG_GREEN); break;
      case 'pur': $this->stdout('[구매]',Console::FG_BLUE); break;
      default: return;
      }
      $this->stdout($v3bidkey->constnm);
      $this->stdout("[$v3bidkey->notinum]($v3bidkey->state,$v3bidkey->bidproc)");
      $this->stdout(empty($v3bidvalue->keyid)?"[NEW]\n":"\n",Console::FG_RED);

      Yii::trace('v3_bid_key: '.VarDumper::dumpAsString($v3bidey->attributes));

      switch($v3bidkey->bidtype){
      case 'con':
        $this->update_con($v3bidkey);
        break;
      case 'ser':
        $this->update_ser($v3bidkey);
        break;
      case 'pur':
        $this->update_pur($v3bidkey);
        break;
      }

    }
    catch(\Exception $e){
      $this->stdout($e,Console::FG_RED);
    }

    $this->stdout(sprintf("[%s] Peak memory usage: %s MB\n",date('Y-m-d H:i:s'),(memory_get_peak_usage(true)/1024/1024)),Console::FG_GREY);
  }

  private function update_con($v3key){
    $this->update_old($v3key,'con');
  }

  private function update_ser($v3key){
    $this->update_old($v3key,'ser');
  }

  private function update_old($v3key,$bidtype){
    if($bidtype==='con'){
      $keyClass=ConKey::className();
      $valClass=ConValue::className();
      $skeyClass=ConSucKey::className();
      $svalClass=ConSucValue::className();
      $succomClass=ConSucCom::className();
    }else if($bidtype==='ser'){
      $keyClass=SerKey::className();
      $valClass=SerValue::className();
      $skeyClass=SerSucKey::className();
      $svalClass=SerSucValue::className();
      $succomClass=SerSucCom::className();
    }
    else return;

    switch($v3key->whereis){
      case '03': $succomClass=KepcoSucCom::className(); break;
      case '05': $succomClass=JuSucCom::className(); break;
      case '10': $succomClass=DpaSucCom::className(); break;
      case '52': $succomClass=KrSucCom::className(); break;
    }

    $v3val=$v3key->v3BidValue;
    $tkey=$keyClass::findNew($v3val->keyid);
    $tkey->attributes=[
      'item_code'=>$v3key->concode,
      'location'=>$v3key->location,
      'constname'=>$v3key->constnm,
      'organization'=>$v3key->org,
      'notinum'=>$v3key->notinum,
      'basic'=>$v3key->basic,
      'presum'=>$v3key->presum,
      'constdate'=>$v3val->constdt,
      'writedate'=>$v3val->writedt,
      'spot_explain'=>$v3val->explaindt,
      'ulevel'=>$v3key->ulevel,
      'in_id'=>$v3key->in_id,
      'state'=>$v3key->state,
      'pct'=>$v3key->pct,
      'level_cnt'=>$v3val->lvcnt,
      'org_code'=>$v3key->orgcode,
      'registdate'=>$v3val->closedt,
    ];
    switch($v3key->whereis){
      case '01': $tkey->whereis='G2B'; break;
      case '03': $tkey->whereis='KEPCO'; break;
      case '08': $tkey->whereis='EX'; break;
      case '10': $tkey->whereis='DPA'; break;
      case '96': $tkey->whereis='KHNP'; break;
      case '52': $tkey->whereis='KR'; break;
      default: $tkey->whereis=$v3key->whereis;
    }
    switch($v3key->whereis){
      case '10': case '07': case '03': case'06': case '93':
        if($v3val->registdt>0) $tkey->registdate=$v3val->registdt;
        break;
    }
    switch($v3key->contract){
      case '10': $tkey->contract_sys='일반'; break;
      case '20': $tkey->contract_sys='제한'; break;
      case '30': $tkey->contract_sys='지명'; break;
      case '40': $tkey->contract_sys='수의'; break;
      case '50': $tkey->contract_sys='장기'; break;
      case '60': $tkey->contract_sys='복수경쟁'; break;
      case '70': $tkey->contract_sys='역경매'; break;
      case '80': $tkey->contract_sys='실적'; break;
      default: $tkey->contract_sys='';
    }
    Yii::trace($keyClass.':'.VarDumper::dumpAsString($tkey->attributes));
    //$tkey->save();

    if($tkey->isNewRecord){
      $v3val->keyid=$tkey->id;
      //$v3val->save();
    }

    $tval=$valClass::findNew($tkey->id);
    $tval->attributes=[
      'parent'=>0,
      'sun'=>0,
      'hyup_enddate'=>$v3val->agreedt,
      'selms'=>'',
      'original_link'=>$v3val->origin_lnk,
      'par_basic'=>$v3val->parbasic,
      'level_cnt'=>$v3val->lvcnt,
      'attatched_file_lnk'=>$v3val->attchd_lnk,
      'filename1'=>'',
      'filename2'=>'',
      'filename3'=>'',
    ];
    if($v3key->whereis=='08'){
      $ms=explode('/',$v3val->multispare);
      foreach($ms as $i=>$m){
        if($i>14) break;
        $tval->{'multispare'.($i+1)}=$m;
      }
    }
    Yii::trace($valClass.': '.VarDumper::dumpAsString($tval->attributes));
    //$tval->save();

    $v3res=$v3key->v3BidResult;
    if($v3res!==null and ArrayHelper::isIn($v3key->bidproc,['S','F'])){
      $tskey=$skeyClass::findNew($v3res->sucid);
      $tskey->attributes=[
        'item_code'=>$tkey->item_code,
        'location'=>$tkey->location,
        'constname'=>$tkey->constname,
        'organization'=>$tkey->organization,
        'notinum'=>$tkey->notinum,
        'contract_sys'=>$tkey->contract_sys,
        'basic'=>$tkey->basic,
        'presum'=>$tkey->presum,
        'registdate'=>$tkey->registdate,
        'org_code'=>$tkey->org_code,
        'level_cnt'=>$tkey->level_cnt,
        'par_basic'=>$tkey->par_basic,
        'pct'=>$tkey->pct,
        'constdate'=>$tkey->constdate,
        'ulevel'=>$tkey->ulevel,
        'yega'=>$v3res->yega,
        'success'=>$v3res->success1,
        'success_name'=>$v3res->officenm1,
        'writedate'=>$v3res->reswdt,
        'in_id'=>$tkey->in_id,
        'whereis'=>$tkey->whereis,
        'state'=>$tkey->state,
      ];
      Yii::trace($skeyClass.': '.VarDumper::dumpAsString($tskey->attributes));
      //$tskey->save();

      if($tskey->isNewRecord){
        $v3res->sucid=$tskey->id;
        //$v3res->save();
      }

      /**
       * XxxSucValue
       */
      $tsval=$svalClass::findNew($tskey->id);
      $tsval->attributes=[
        'selms'=>$v3res->selms,
        'original_link'=>'',
        'filename1'=>$tval->filename1,
        'filename2'=>$tval->filename2,
        'filename3'=>$tval->filename3,
      ];
      $ms=explode('/',$v3val->multispare);
      foreach($ms as $i=>$m){
        if($i>14) break;
        $tsval->{'multispare'.($i+1)}=$m;
      }
      Yii::trace($svalClass.': '.VarDumper::dumpAsString($tsval->attributes));
      //$tsval->save();

      /**
       * XxxSucCom
       */
      //$succomClass::deleteAll(['id'=>$tskey->id]);
      //KepcoSucCom::deleteAll(['id'=>$tskey->id,'bidtype'=>$bidtype]);
      //JuSucCom::deleteAll(['id'=>$tskey->id]);
      //DpaSucCom::deleteAll(['id'=>$tskey->id,'bidtype'=>$bidtype]);
      //KrSucCom::deleteAll(['id'=>$tskey->id]);
      $succoms=V3BidSuccom::findAll([
        'constdate'=>$v3key->constdate,
        'bidid'=>$v3key->bidid,
      ]);
      $innum=count($succoms);
      $n=1;
      Console::startProgress(0,$innum);
      foreach($succoms as $s){
        $tsuccom=null;
        switch($v3key->whereis){
          case '03':
            $tsuccom=new KepcoSucCom([
              'id'=>$tskey->id,
              'bidtype'=>$bidtype,
              'seq'=>$s->seq,
              'officeno'=>$s->officeno,
              'officename'=>$s->officenm,
              'pre_name'=>$s->prenm,
              'success'=>$s->success,
              'pct'=>$s->pct,
            ]);
            break;
          case '05':
            if($bidtype==='con'){
              $tsuccom=new JuSucCom([
                'id'=>$tskey->id,
                'officeno'=>$s->officeno,
                'officename'=>$s->officenm,
                'pre_name'=>$s->prenm,
                'rank'=>$s->rank,
                'success'=>$s->success,
                'pct'=>$s->pct,
                'result'=>$s->etc,
              ]);
            }
            break;
          case '10':
            $tsuccom=new DpaSucCom([
              'id'=>$tskey->id,
              'bidtype'=>$bidtype,
              'officecode'=>$s->officeno,
              'seq'=>$s->seq,
              'officeno'=>$s->officeno,
              'officename'=>$s->officenm,
              'pre_name'=>$s->prenm,
              'success'=>$s->success,
              'pct'=>$s->pct,
              'result'=>$s->etc,
            ]);
            break;
          case '52':
            if($bidtype==='con'){
              $tsuccom=new KrSucCom([
                'id'=>$tskey->id,
                'officeno'=>$s->officeno,
                'officename'=>$s->officenm,
                'pre_name'=>$s->prenm,
                'rank'=>$s->rank,
                'success'=>$s->success,
                'pct'=>$s->pct,
              ]);
            }
            break;
          default:
            $tsuccom=new $succomClass([
              'id'=>$tskey->id,
              'officeno'=>$s->officeno,
              'officename'=>$s->officenm,
              'pre_name'=>$s->prenm,
              'rank'=>$s->rank,
              'success'=>$s->success,
              'pct'=>$s->pct,
            ]);
        }
        if($tsuccom!==null){
          //$tsuccom->save();
          Yii::trace($succomClass.': '.VarDumper::dumpAsString($tsuccom->attributes));
        }
        Console::updateProgress($n,$innum);
        $n++;
      }
      Console::endProgress();
    }
  }

  private function update_pur($v3key){
    if(empty($v3key->writedate) or $v3key->writedate=='0000-00-00') return;
    $v3val=$v3key->v3BidValue;
    $keyid=$v3val->keyid;
    list($bidno,$bidseq,$rebidno,$divno)=explode('-',$v3key->bidid);
    $subseq=intval($bidseq);

    $pm=PurMaster::findOne([
      'id'=>$keyid,
      'subseq'=>$subseq,
    ]);
    if($pm===null or $keyid==0){
      $pm=new PurMaster;
      $idstart=str_replace('-','',$v3key->writedate);
      $id=(new Query())->from('pur_master')
        ->where("id like ':idstart%'",[':idstart'=>$idstart])
        ->max('id',PurMaster::getDb());
      if(empty($id)) $id=$idstart.'0000';
      $pm->id=$id;
      $pm->subseq=$subseq;
    }

    $pm->attributes=[
      'notinum'=>$v3key->notinum,
      'constname'=>$v3key->constnm,
      'org'=>$v3key->org,
      'gesi_dt'=>$v3val->noticedt,
      'explain_dt'=>$v3val->explaindt,
      'ibchalgesi_dt'=>$v3val->opendt,
      'ibchalmagam_dt'=>$v3val->closedt,
      'ibchal_dt'=>$v3val->constdt,
      'chamgamagam_dt'=>$v3val->registdt,
      'write_dt'=>$v3val->writedt,
      'presum'=>$v3key->presum,
      'basic'=>$v3key->basic,
      'pct'=>$v3key->pct,
      'itemcode'=>$v3key->purcode,
      'location'=>$v3key->location,
      'state'=>$v3key->state,
      'register'=>'i2conv',
      'islast'=>'Y',
      'org_code'=>$v3key->orgcode,
    ];

    if(empty($pm->chamgamagam_dt) or $v3key->whereis==='01'){
      $pm->chamgamagam_dt=$pm->ibchalmagam_dt;
    }
    switch($v3key->contract){
    case '10': $pm->constract='일반'; break;
    case '20': $pm->constract='제한'; break;
    case '40': $pm->constract='수의'; break;
    case '70': $pm->constract='역경매'; break;
    case '80': $pm->constract='실적'; break;
    default: $pm->constract='';
    }
    switch($v3key->bidcls){
    case '00': $pm->ibchalbangsik='직찰'; break;
    case '01': $pm->ibchalbangsik='전자입찰'; break;
    default: $pm->ibchalbangsik='';
    }

    $cmtopts=[];
    if(($v3key->ulevel&pow(2,2))>0) $cmtopts[]='긴급';
    if(($v3key->ulevel&pow(2,11))>0) $cmtopts[]='관내';
    $pm->commentoption=implode(',',$cmtopts);

    switch($v3key->bidproc){
    case 'B': $pm->dataprocess='GEN'; break;
    case 'C': $pm->dataprocess='CANCEL'; break;
    default: $pm->dataprocess='MOD';
    }

    if(!ArrayHelper::isIn($v3key->bidproc,['S','F']) && intval($rebidno)>0){
      $pm->isjeibchal='Y';
    }

    switch($v3key->whereis){
    case '01': $pm->whereis='G2B'; break;
    case '08': $pm->whereis='EX'; break;
    case '03': $pm->whereis='KEPCO'; break;
    case '10': $pm->whereis='DPA'; break;
    case '96': $pm->whereis='KHNP'; break;
    case '52': $pm->whereis='KR'; break;
    default: $pm->whereis='ETC';
    }

    $v3bidgoods=V3BidGoods::findAll(['bidid'=>$v3key->bidid]);
    $pm->goods_cnt=count($v3bidgoods);

    $v3bidlocals=$v3key->v3BidLocals;
    $lockeywords=[];
    foreach($v3bidlocals as $v3bidlocal){
      $a=explode(' ',$v3bidlocal->name);
      $lockeywords[]=array_pop($a);
    }
    $pm->lockeyword=implode(',',$lockeywords);

    Yii::trace('pur_master: '.VarDumper::dumpAsString($pm->attributes),'legacy');
    //$pm->save();

    $v3ctn=$v3key->v3BidContent;

    $pfd=$pm->purFileData;
    if($pfd===null){
      $pfd=new PurFileData([
        'id'=>$pm->id,
        'subseq'=>$pm->subseq,
      ]);
    }
    if($v3ctn!==null){
      $pfd->attributes=[
        'filedata1'=>$v3ctn->upfile_bid,
        'jungjungdata'=>$v3ctn->important_bid,
        'maincontents'=>$v3ctn->content_bid,
        'openbid_contents'=>$v3ctn->content_suc,
      ];
    }
    $pfd->urlinfo1=$v3val->origin_lnk;
    $pfd->linkdata=$v3val->attchd_lnk;
    //$pfd->save();

    $v3goods=$v3key->v3BidGoods;
    foreach($v3goods as $g){
      $pgg=PurG2bGoods::findOne([
        'notinum'=>$v3key->notinum,
        'bunryu_no'=>1,
        'sunbun_no'=>$g->seq,
      ]);
      if($pgg===null){
        $pgg=new PurG2bGoods([
          'notinum'=>$v3key->notinum,
          'bunryu_no'=>1,
          'sunbun_no'=>$g->seq,
        ]);
      }
      $pgg->attributes=[
        'info_code'=>0,
        'info_name'=>'',
        'g2b_code'=>$g->gcode,
        'g2b_myung'=>$g->gname,
      ];
      Yii::trace('pur_g2b_goods: '.VarDumper::dumpAsString($pgg->attributes),'legacy');
      //$pgg->save();
    }

    $v3res=$v3key->v3BidResult;
    if($v3res!==null and ArrayHelper::isIn($v3key->bidproc,['R','S','F'])){
      $pres=PurRes::findOne([
        'id'=>$pm->id,
        'notinum'=>$pm->notinum,
        'bunryuno'=>1,
      ]);
      if($pres===null){
        $pres=new PurRes([
          'id'=>$pm->id,
          'notinum'=>$pm->notinum,
          'bunryuno'=>1,
        ]);
      }
      $pres->attributes=[
        'jeibchal_bunho'=>intval($rebidno),
        'rs_selected_num'=>$v3res->selms,
        'rs_gechal_ilsi'=>$v3res->resdt,
        'yega'=>$v3res->yega,
        'gichoamt'=>$v3key->basic,
      ];
      switch($v3key->bidproc){
      case 'R': $pres->process='REBID'; break;
      case 'F': $pres->process='YUCHAL'; break;
      default: $pres->process='DONE';
      }
      $ms=explode('/',$v3val->multispare);
      if(isset($ms[0])) $pres->rs_yega1=$ms[0];
      if(isset($ms[1])) $pres->rs_yega2=$ms[1];
      if(isset($ms[2])) $pres->rs_yega3=$ms[2];
      if(isset($ms[3])) $pres->rs_yega4=$ms[3];
      if(isset($ms[4])) $pres->rs_yega5=$ms[4];
      if(isset($ms[5])) $pres->rs_yega6=$ms[5];
      if(isset($ms[6])) $pres->rs_yega7=$ms[6];
      if(isset($ms[7])) $pres->rs_yega8=$ms[7];
      if(isset($ms[8])) $pres->rs_yega9=$ms[8];
      if(isset($ms[9])) $pres->rs_yega10=$ms[9];
      if(isset($ms[10])) $pres->rs_yega11=$ms[10];
      if(isset($ms[11])) $pres->rs_yega12=$ms[11];
      if(isset($ms[12])) $pres->rs_yega13=$ms[12];
      if(isset($ms[13])) $pres->rs_yega14=$ms[13];
      if(isset($ms[14])) $pres->rs_yega15=$ms[14];

      switch($v3key->bidproc){
      case 'F': case 'S': $pm->sucprocess='Y'; break;
      case 'R': $pm->isjeibchal='Y'; break;
      }
      $pm->successamt=$v3res->success1;
      $pm->successname=$v3res->officenm1;

      Yii::trace('pur_res: '.VarDumper::dumpAsString($pres->attributes),'legacy');

      if($v3key->bidproc==='S'){
        $v3succoms=V3BidSuccom::findAll([
          'constdate'=>$v3key->constdate,
          'bidid'=>$v3key->bidid,
        ]);
        //PurResEnterprise::deleteAll(['id'=>$pm->id]);
        $innum=count($v3succoms);
        $n=1;
        Console::startProgress(0,$innum);
        foreach($v3succoms as $s){
          $pen=new PurResEnterprise([
            'id'=>$pm->id,
            'saupja_bunho'=>$s->officeno,
            'notinum'=>$pm->notinum,
            'sunwi'=>$s->rank,
            'upche_myung'=>$s->officenm,
            'depyoja'=>$s->prenm,
            'tuchal_gumek'=>$s->success,
            'tuchal_ryul'=>$s->pct,
            'bigo'=>$s->etc,
            'bunryuno'=>1,
          ]);
          Yii::trace('pur_res_enterprise: '.VarDumper::dumpAsString($pen->attributes),'legacy');
          //$pen->save();
          Console::updateProgress($n,$innum);
          $n++;
        }
        Console::endProgress();
      }
    }

    $v3val->keyid=$pm->id;
    //$v3val->save();
  }
}

