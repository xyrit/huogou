<?php

namespace app\models;

use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "user_address".
 *
 * @property integer $id
 * @property string $address
 * @property string $area
 * @property string $code
 * @property string $name
 * @property string $telephone
 * @property string $mobilephone
 * @property integer $create
 * @property integer $update
 * @property integer $uid
 */
class UserAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'name', 'mobilephone', 'uid'], 'required'],
            [['create', 'update', 'uid'], 'integer'],
            [['address', 'area'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 30],
            [['telephone'], 'string', 'max' => 50],
            [['mobilephone'], 'string', 'max' => 11]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'address' => '所在地区',
            'area' => '详细地址',
            'code' => '邮政编码',
            'name' => '收货人',
            'telephone' => '固定电话',
            'mobilephone' => '手机号码',
            'create' => '添加时间',
            'update' => '更新时间',
            'uid' => '用户ID',
        ];
    }

    public static function editAddress($model, $params)
    {
        $model->setAttributes($params,false);

        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($model->default_address_status) {
                UserAddress::updateAll(['default_address_status' => 0], ['uid' => $params['user_id'], 'default_address_status' => 1]);
            }

            if (!isset($model->id)) {
                $model->uid = $params['user_id'];
                $model->create = time();
                $model->status = 1;
            } else {
                $model->update = time();
                $model->status = 0;
            }

            if (!$model->save()) {
                $trans->rollBack();
                return false;
            }

            $trans->commit();
            return $model->id;
        } catch (Exception $e) {
            $trans->rollBack();
            return false;
        }

    }
}