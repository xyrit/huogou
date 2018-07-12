<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/11/16
 * Time: 下午2:13
 */
namespace app\queue;

use yii\helpers\Json;

abstract class BaseQueue
{

    public $args;

    public function __construct($data)
    {
        $this->args = $data;
        $this->init();
    }

    public function init()
    {
        return true;
    }

    abstract public function run();

}