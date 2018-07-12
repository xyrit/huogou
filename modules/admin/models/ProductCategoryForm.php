<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/25
 * Time: 上午10:16
 */
namespace app\modules\admin\models;

use app\models\ProductCategory;
use Yii;
use yii\base\Model;


class ProductCategoryForm extends Model
{
    public $parent_id;
    public $name;
    public $list_order;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'parent_id'], 'required'],
            [['parent_id', 'list_order'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['name', 'validateName']
        ];
    }

    public function attributeLabels()
    {
        return [
            'parent_id' => '上级分类',
            'name' => '分类名称',
            'list_order' => '排序',
        ];
    }

    public function validateName($attribute)
    {
        $model = ProductCategory::findOne(['name'=>$this->name]);
        if ($model) {
            $this->addError($attribute, '分类已存在.');
        }

        return;
    }
}