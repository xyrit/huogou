<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/21
 * Time: 上午11:32
 */
namespace app\modules\admin\models;

use Yii;
use yii\base\Model;


class ProductTypeForm extends Model
{
    public $name;
    public $alias;
    public $brands;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'brands'], 'required'],
            [['alias'], 'string', 'max' => 255]
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '类型名称',
            'alias' => '类型别名',
            'brands' => '关联品牌',
        ];
    }


}