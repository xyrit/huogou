<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\helpers\Sundry;

/**
 * This is the model class for table "brands".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 * @property string $url
 * @property string $intro
 */
class Brand extends \yii\db\ActiveRecord
{   
    /**
     * type list
     */
    const BRANDLIST = 'BRANDLIST';
    const BRANDLIST_TYPE = 'string';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'brands';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['url'], 'url'],
            [['intro'], 'string'],
            [['name', 'alias', 'url'], 'string', 'max' => 255],
            ['name', 'validateName' ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'alias' => '别名',
            'url' => '网址',
            'intro' => '介绍',
        ];
    }

    public function validateName($attribute)
    {
        $bname = Brand::findOne($this->id);
        if(strcmp($bname['name'], $this->name) == 0){
            $model = Brand::findOne(['name'=>$this->name]);
            if ($model) {
                $this->addError($attribute, '品牌已存在.');
            }
        }

        return;
    }

    /**
     * 获取品牌列表
     * @param  int $cid CategoryId
     * @return [type]      [description]
     */
    public static function getBrandByCid($catId = ''){
        $data = self::getData(self::BRANDLIST.$catId,self::BRANDLIST_TYPE);
        if (!$data) {
            $_data = static::find()->all();
            $_data = ArrayHelper::toArray($_data);
            foreach ($_data as $key => $value) {
                $data[$value['id']] = Sundry::jsonEncodeCn($value);
            }
            self::setData(self::BRANDLIST,json_encode($data),self::BRANDLIST_TYPE);
        }
        return Sundry::jsonDecodeCn($data);
    }
}