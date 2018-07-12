<?php
/**
 * @category  huogou.com
 * @name  AppConfig
 * @version 1.0
 * @date 2015-12-29
 * @author  keli <liwanglai@gmail.com>
 * 
 */
namespace app\models;

use Yii;

class AppConfigLog extends AppConfig
{
     /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_config_log';
    }
    public function rules()  {return [];}
    public function afterSave($insert, $changedAttributes)     {  }
}