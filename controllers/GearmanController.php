<?php
namespace i2conv\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\db\Query;

use GearmanWorker;
use GearmanClient;
use Redis;

use i2conv\models\i2\BidKey;
use i2conv\models\i2\BidValue;
use i2conv\models\i2\BidGoods;
use i2conv\models\i2\BidLocal;

use i2conv\models\info21c\V3BidKey;
use i2conv\models\info21c\V3BidValue;
use i2conv\models\info21c\V3BidContent;
use i2conv\models\info21c\V3BidGoods;
use i2conv\models\info21c\V3BidItemcode;
use i2conv\models\info21c\V3BidLocal;
use i2conv\models\info21c\V3BidResult;
use i2conv\models\info21c\V3BidSuccom;
use i2conv\models\info21c\V3BidSubcode;

class GearmanController extends \yii\console\Controller
{
  protected $redis;

  public function init(){
    parent::init();
    $this->redis=new Redis();
  }

  public function actionSink(){
    $worker=new GearmanWorker();
    $worker->addServers($this->module->gman_server);
    $worker->addFunction('i2conv',[$this,'i2conv_sink']);
    while($worker->work());
  }

  public function actionFlush(){
    $gman_client=new GearmanClient;
    $gman_client->addServers($this->module->gman_server);
    while(true){
      $this->redis->pconnect($this->module->redis_server);
      $workloads=$this->redis->sMembers('i2conv.workloads');
      foreach($workloads as $workload){
        echo $workload,PHP_EOL;
        $gman_client->doBackground('i2conv_start',$workload);
      }
      $this->redis->delete('i2conv.workloads');
      echo Console::renderColoredString('%y'.date('Y-m-d H:i:s').'] start count : '.count($workloads).'%n'),PHP_EOL;
      sleep(3);
    }
  }

  public function actionWork(){
    ini_set('memory_limit','128M');
    $worker=new GearmanWorker();
    $worker->addServers($this->module->gman_server);
    $worker->addFunction('i2conv_start',[$this,'i2conv_run']);
    while($worker->work());
  }

  public function i2conv_sink($job){
    $workload=$job->workload();
    $this->redis->pconnect($this->module->redis_server);
    $this->redis->sAdd('i2conv.workloads',$workload);
    echo date('Y-m-d H:i:s').'] '.$workload,PHP_EOL;
  }

  public function i2conv_run($job){
    $workload=$job->workload();
    $workload=Json::decode($workload);

    try {

      $this->module->i2db->close();
      $this->module->infodb->close();

      $bidKey=BidKey::findOne($workload['bidid']);
      if($bidKey===null) return;
      $bidvalue=$bidKey->bidValue;
      if($bidvalue===null) return;

      if(!ArrayHelper::isIn($bidKey->state,['Y','N','D'])) return;
      if($bidKey->bidproc==='J') return;
      if(empty($bidKey->location)) return;

      switch($bidKey->bidtype){
      case 'con': echo Console::renderColoredString('%y[공사]%n'); break;
      case 'ser': echo Console::renderColoredString('%g[용역]%n'); break;
      case 'pur': echo Console::renderColoredString('%b[구매]%n'); break;
      default: return;
      }

      echo $bidKey->constnm;
      echo '['.$bidKey->notinum.']';
      echo '('.$bidKey->state.','.$bidKey->bidproc.')';

      //------------------------------------------------------
      // v3_bid_key
      //------------------------------------------------------
      $v3bidkey=V3BidKey::findNew($bidKey->bidid);
      $this->stdout(($v3bidkey->isNewRecord)?"[NEW]\n":"\n",Console::FG_RED);
      $v3bidkey->attributes=[
        'whereis' =>$bidKey->whereis,
        'bidtype' =>$bidKey->bidtype,
        'con'     =>strpos($bidKey->bidview,'con')===false?'N':'Y',
        'ser'     =>strpos($bidKey->bidview,'ser')===false?'N':'Y',
        'pur'     =>strpos($bidKey->bidview,'pur')===false?'N':'Y',
        'notinum' =>$bidKey->notinum,
        'orgcode' =>$bidKey->orgcode_i,
        'constnm' =>$bidKey->constnm,
        'org'     =>$bidKey->org_i,
        'bidproc' =>$bidKey->bidproc,
        'contract'=>$bidKey->contract,
        'bidcls'  =>$bidKey->bidcls,
        'succls'  =>$bidKey->succls,
        'conlevel'=>$bidKey->toV3BidKey_conlevel(),
        'ulevel'  =>$bidKey->opt,
        'concode' =>$bidKey->toV3BidKey_concode(),
        'sercode' =>$bidKey->toV3BidKey_sercode(),
        'purcode' =>$bidKey->toV3BidKey_purcode(),
        'location'=>$bidKey->location?$bidKey->location:0,
        'convention'=>$bidKey->convention=='3'?'2':$bidKey->convention,
        'presum'  =>$bidKey->presum?$bidKey->presum:0,
        'basic'   =>$bidKey->basic?$bidKey->basic:0,
        'pct'     =>$bidKey->pct?$bidKey->pct:'',
        'registdate'  =>strtotime($bidKey->registdt)>0? date('Y-m-d',strtotime($bidKey->registdt)):'',
        'explaindate' =>strtotime($bidKey->explaindt)>0?date('Y-m-d',strtotime($bidKey->explaindt)):'',
        'agreedate'   =>strtotime($bidKey->agreedt)>0?  date('Y-m-d',strtotime($bidKey->agreedt)):'',
        'opendate'    =>strtotime($bidKey->opendt)>0?   date('Y-m-d',strtotime($bidKey->opendt)):'',
        'closedate'   =>strtotime($bidKey->closedt)>0?  date('Y-m-d',strtotime($bidKey->closedt)):'',
        'constdate'   =>strtotime($bidKey->constdt)>0?  date('Y-m-d',strtotime($bidKey->constdt)):'',
        'writedate'   =>strtotime($bidKey->writedt)>0?  date('Y-m-d',strtotime($bidKey->writedt)):'',
        'reswdate'    =>strtotime($bidKey->resdt)>0?    date('Y-m-d',strtotime($bidKey->resdt)):'',
        'state'=>$bidKey->state,
        'in_id'=>91,
      ];

      //------------------------------------------------------
      // v3_bid_value
      //------------------------------------------------------
      $v3BidValue=V3BidValue::findNew($v3bidkey->bidid);
      $v3BidValue->attributes=[
        'scrcls'    =>$bidvalue->scrcls,
        'scrid'     =>$bidvalue->scrid,
        'constno'   =>$bidvalue->constno,
        'refno'     =>$bidvalue->refno,
        'realorg'   =>$bidvalue->realorg,
        'yegatype'  =>$bidvalue->yegatype,
        'yegarng'   =>str_replace('|','/',$bidvalue->yegarng),
        'prevamt'   =>$bidvalue->prevamt,
        'parbasic'  =>$bidvalue->parbasic,
        'lvcnt'     =>$bidvalue->lvcnt,
        'charger'   =>str_replace('|','/',$bidvalue->charger),
        'multispare'=>str_replace('|','/',str_replace(',','',$bidvalue->multispare)),
        'contper'   =>$bidvalue->contper,
        'noticedt'  =>strtotime($bidKey->noticedt)>0?strtotime($bidKey->noticedt):0,
        'registdt'  =>strtotime($bidKey->registdt)>0?strtotime($bidKey->registdt):0,
        'explaindt' =>strtotime($bidKey->explaindt)>0?strtotime($bidKey->explaindt):0,
        'agreedt'   =>strtotime($bidKey->agreedt)>0 ?strtotime($bidKey->agreedt):0,
        'opendt'    =>strtotime($bidKey->opendt)>0  ?strtotime($bidKey->opendt):0,
        'closedt'   =>strtotime($bidKey->closedt)>0 ?strtotime($bidKey->closedt):0,
        'constdt'   =>strtotime($bidKey->constdt)>0 ?strtotime($bidKey->constdt):0,
        'writedt'   =>strtotime($bidKey->writedt)>0 ?strtotime($bidKey->writedt):0,
        'editdt'    =>strtotime($bidKey->editdt)>0  ?strtotime($bidKey->editdt):0,
      ];
      //공동도급지역코드 (사용하나??)
      $arr=explode('|',$bidvalue->contloc);
      foreach($arr as $val){
        if(empty($val)) continue;
        $m=BidLocal::findOne(['bidid'=>$v3bidkey->bidid,'name'=>iconv('utf-8','euckr',$val)]);
        if($m!==null){
          $v3BidValue->contloc=$m->code;
          break; //v3_bid_key.contloc char(4) 때문 1개 지역만 처리...
        }
      }


      //------------------------------------------------------
      // v3_bid_itemcode
      //------------------------------------------------------
      if(ArrayHelper::keyExists('save',$workload)) V3BidItemcode::deleteAll(['bidid'=>$v3bidkey->bidid]);
      $bidItemcodes=$bidKey->toV3BidItemcodes_attributes();
      foreach($bidItemcodes as $row){
        $v3BidItemcode=V3BidItemcode::findNew($v3bidkey->bidid,$row['bidtype'],$row['code']);
        $v3BidItemcode->name=$row['name'];
        Yii::trace('v3_bid_itemcode: '.VarDumper::dumpAsString($v3BidItemcode->attributes),'i2conv');
        if(ArrayHelper::keyExists('save',$workload)) $v3BidItemcode->save();
      }

      //------------------------------------------------------
      // v3_bid_local
      //------------------------------------------------------
      if(ArrayHelper::keyExists('save',$workload)) V3BidLocal::deleteAll(['bidid'=>$v3bidkey->bidid]);
      $bidlocals=$bidKey->bidLocals;
      foreach($bidlocals as $bidlocal){
        $v3BidLocal=new V3BidLocal([
          'bidid' =>$v3bidkey->bidid,
          'code'  =>$bidLocal->code,
          'name'  =>$bidLocal->name,
        ]);
        Yii::trace('v3_bid_local: '.VarDumper::dumpAsString($v3BidLocal->attributes),'i2conv');
        if(ArrayHelper::keyExists('save',$workload)) $v3BidLocal->save();
      }

      //------------------------------------------------------
      // v3_bid_subcode
      //------------------------------------------------------
      if(ArrayHelper::keyExists('save',$workload)) V3BidSubcode::deleteAll(['bidid'=>$v3bidkey->bidid]);
      $subcodes=$bidKey->bidSubcodes;
      foreach($subcodes as $subcode){
        $v3BidSubcode=new V3BidSubcode([
          'bidid'   =>$v3bidkey->bidid,
          'g2b_code'=>$subcode->g2b_code,
          'g2b_code_nm'=>$subcode->g2b_code_nm,
          'i2_code' =>$subcode->i2_code,
          'itemcode'=>$subcode->itemcode,
          'pri_cont'=>$subcode->pri_cont,
          'share'   =>$subcode->share,
        ]);
        Yii::trace('v3_bid_subcode: '.VarDumper::dumpAsString($v3BidSubcode->attributes),'i2conv');
        if(ArrayHelper::keyExists('save',$workload)) $v3BidSubcode->save();
      }


      //-------------------------------------------------------
      // v3_bid_content
      //-------------------------------------------------------
      $bidcontent=$bidKey->bidContent;
      if($bidcontent!==null){
        $v3content=V3BidContent::findNew($v3bidkey->bidid);
        $v3content->attributes=[
          'content_bid'=>$bidcontent->bid_html,
          'important_suc'=>$bidcontent->nbidcomment,
          'content_suc'=>$bidcontent->nbid_html,
          'upfile_bid'=>$bidcontent->bid_file,
          'upfile_suc'=>$bidcontent->nbid_file,
          'important_bid'=>!empty($bidcontent->bidcomment_mod)?$bidcontent->bidcomment_mod.'\n<hr/>\n'.$bidcontent->bidcomment:$bidcontent->bidcomment,
        ];
        if(ArrayHelper::keyExists('save',$workload)) $v3content->save();

        $v3BidValue->origin_lnk=$bidcontent->orign_lnk;
        $v3BidValue->attchd_lnk=$bidcontent->attchd_lnk;
      }

      //-----------------------------------------------------
      // v3_bid_goods
      //-----------------------------------------------------
      $bidGoods=$bidKey->bidGoods;
      if(ArrayHelper::keyExists('save',$workload)) V3BidGoods::deleteAll(['bidid'=>$v3bidkey->bidid]);
      foreach($bidGoods as $g){
        $v3BidGood=new V3BidGoods;
        $v3BidGood->attributes=$g->attributes;
        Yii::trace('v3_bid_goods: '.VarDumper::dumpAsString($v3BidGood->attributes),'i2conv');
        if(ArrayHelper::keyExists('save',$workload)) $v3BidGood->save();
      }

      //-------------------------------------------------------
      // v3_bid_res,v3_bid_succom
      //-------------------------------------------------------
      $bidRes=$bidKey->bidRes;
      if(ArrayHelper::isIn($bidKey->bidproc,['S','F']) && $bidRes!==null)
      {
        $v3BidResult=V3BidResult::findNew($v3bidkey->bidid);
        $v3BidResult->attributes=[
          'yega'      =>$bidRes->yega,
          'innum'     =>$bidRes->innum,
          'officenm1' =>$bidRes->officenm1,
          'prenm1'    =>$bidRes->prenm1,
          'officeno1' =>$bidRes->officeno1,
          'success1'  =>$bidRes->success1,
          'resdt'     =>(strtotime($bidKey->resdt)>0)?strtotime($bidKey->resdt):0,
          'reswdt'    =>(strtotime($bidRes->reswdt)>0)?strtotime($bidRes->reswdt):0,
        ];
        $arr=explode('|',$bidREs->selms);
        $selms=[];
        foreach($arr as $v){
          if($v=='') continue;
          $selms[]=intval($v)-1;
        }
        $v3BidResult->selms=implode('-',$selms);
        Yii::trace('v3_bid_result: '.VarDumper::dumpAsString($v3BidResult->attributes),'i2conv');
        if(ArrayHelper::keyExists('save',$workload)) $v3BidResult->save();

        if($bidKey->whereis!='08' && $bidKey->bidproc!='F'){
          $v3BidValue->multispare=str_replace('|','/',str_replace(',','',$bidRes->multispare));
        }
        if(ArrayHelper::keyExists('save',$workload)) $v3BidValue->save();
        
        if($bidRes->innum>0){
          $succoms=$bidKey->succoms;
          if(count($succoms)==$bidRes->innum){
            if(ArrayHelper::keyExists('save',$workload)) V3BidSuccom::deleteAll(['constdate'=>$v3bidkey->constdate,'bidid'=>$v3bidkey->bidid]);
            Console::startProgress(0,$bidRes->innum);
            $n=1;
            foreach($succoms as $s){
              $v3succom=V3BidSuccom::findNew($v3bidkey->constdate,$v3bidkey->bidid,$s->seq);
              $v3succom=new V3BidSuccom([
                'constdate' =>$v3bidkey->constdate,
                'bidid'     =>$v3bidkey->bidid,
                'seq'       =>$s->seq,
                'regdt'     =>$s->regdt,
                'pct'       =>$s->pct,
                'prenm'     =>$s->prenm,
                'officenm'  =>$s->officenm,
                'officeno'  =>$s->officeno,
                'success'   =>$s->success,
                'etc'       =>$s->etc,
                'rank'      =>$s->rank,
              ]);
              Yii::trace('v3_bid_succom: '.VarDumper::dumpAsString($v3succom->attributes),'i2conv');
              if(ArrayHelper::keyExists('save',$workload)) $v3succom->save();

              Console::updateProgress($n,$bidRes->innum);
              $n++;
            }
            Console::endProgress();
          }
        }
      }

      Yii::trace('v3_bid_value: '.VarDumper::dumpAsString($v3BidValue->attributes),'i2conv');
      Yii::trace('v3_bid_key: '.VarDumper::dumpAsString($v3bidkey->attributes),'i2conv');
      if(ArrayHelper::keyExists('save',$workload)) $v3BidValue->save();
      if(ArrayHelper::keyExists('save',$workload)) $v3bidkey->save();

      $gman_client=new GearmanClient;
      $gman_client->addServers($this->module->gman_server);
      $gman_client->doBackground('i2conv_legacy',Json::encode($workload));
    }
    catch(\Exception $e){
      echo Console::ansiFormat($e,[Console::FG_RED]),PHP_EOL;
      exit;
    }

    $this->stdout(sprintf("[%s] Peak memory usage: %s MB\n",date('Y-m-d H:i:s'),(memory_get_peak_usage(true)/1024/1024)),Console::FG_GREY);
  }
}

