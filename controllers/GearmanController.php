<?php
namespace i2conv\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;

use GearmanWorker;

use i2conv\models\i2\BidKey;
use i2conv\models\i2\BidValue;
use i2conv\models\i2\BidGoods;

use i2conv\models\info21c\V3BidKey;

class GearmanController extends \yii\console\Controller
{
  public function actionIndex(){
    ini_set('memory_limit','128M');
    echo '[database connection]',PHP_EOL;
    echo '  i2db   : '.$this->module->i2db->dsn,PHP_EOL;
    echo '  infodb : '.$this->module->infodb->dsn,PHP_EOL;
    echo '[gearman server]',PHP_EOL;
    echo '  server   : '.$this->module->gman_server,PHP_EOL;
    echo '  function : "i2conv"',PHP_EOL;
    echo Console::renderColoredString('%yStart worker...%n'),PHP_EOL;

    $worker=new GearmanWorker();
    $worker->addServers($this->module->gman_server);
    $worker->addFunction('i2conv',[$this,'i2conv']);
    while($worker->work());
  }

  public function i2conv($job){
    $workload=$job->workload();
    $workload=Json::decode($workload);

    try {

      $this->module->i2db->close();
      $this->module->infodb->close();

      $bidKey=BidKey::findOne($workload['bidid']);
      if($bidKey===null) return;

      if(!ArrayHelper::isIn($bidKey->state,['Y','N','D'])) return;
      if($bidKey->bidproc==='J') return;
      if(empty($bidKey->location)) return;

      switch($bidKey->bidtype){
      case 'con':
        echo Console::renderColoredString('%y[공사]%n'); break;
      case 'ser':
        echo Console::renderColoredString('%g[용역]%n'); break;
      case 'pur':
        echo Console::renderColoredString('%b[구매]%n'); break;
      default: return;
      }
      echo $bidKey->constnm;
      echo '['.$bidKey->notinum.']';
      echo '(state:'.$bidKey->state.',bidproc:'.$bidKey->bidproc.',isclosed:'.$bidKey->isclosed.',bidview:'.$bidKey->bidview;
      echo ')',PHP_EOL;

      $v3bidkey=V3BidKey::findOne($bidKey->bidid);
      if($v3bidkey===null){
        $v3bidkey=new V3BidKey(['bidid'=>$bidKey->bidid]);
      }
      //print_r($bidKey->toV3BidKeyAttributes());

      $bidvalue=BidValue::findOne($bidKey->bidid);
      if($bidvalue!==null){
        //print_r($bidvalue->toV3BidValueAttributes());
      }

      $bidGoods=BidGoods::findAll(['bidid'=>$bidKey->bidid]);
      foreach($bidGoods as $g){
        echo implode(',',$g->toV3BidGoodsAttributes()),PHP_EOL;
      }
      $goods_count=count($bidGoods);
      if($goods_count>0){
        //goods_count보다큰거는 삭제
      }

      if($bidKey->state==='Y' and $bidKey->bidproc==='S'){
        $bidRes=$bidKey->res;
        if($bidRes->innum>0){
          //print_r($bidRes->toV3BidResultAttributes());
          //print_r($bidRes->toV3BidValueAttributes());

          $succoms=$bidKey->succoms;
          if(count($succoms)==$bidRes->innum){
            echo ' > succoms:';
            foreach($succoms as $succom){
              echo "$succom->seq,";
            }
            echo 'OK',PHP_EOL;
          }
        }
      }


    }
    catch(\Exception $e){
      echo \yii\helpers\Console::ansiFormat($e,[\yii\helpers\Console::FG_RED]),PHP_EOL;
      exit;
    }

    $this->stdout(sprintf("[%s] Peak memory usage: %s MB\n",date('Y-m-d H:i:s'),(memory_get_peak_usage(true)/1024/1024)),Console::FG_YELLOW);
  }
}

