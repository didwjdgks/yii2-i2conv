<?php
namespace i2conv;

use yii\db\Connection;
use yii\di\Instance;

class Module extends \yii\base\Module
{
  public $i2db='i2db';
  public $infodb='infodb';

  public $gman_server;
  public $redis_server;

  public function init(){
    parent::init();

    $this->i2db=Instance::ensure($this->i2db,Connection::className());
    $this->infodb=Instance::ensure($this->infodb,Connection::className());
  }
}

