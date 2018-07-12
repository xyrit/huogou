<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/20
 * Time: 下午4:49
 */
namespace app\modules\admin\models;

use yii\base\Model;

class OrderManageGroupForm extends Model
{
    public $name;
    public $userIds;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            [['name', 'userIds'], 'required'],

            [['name'], 'string', 'max'=>30],
        ];
    }
    public function attributeLabels()
    {
        return [
            'name' => '小组名称',
            'userIds' => '小组成员',
        ];
    }

}