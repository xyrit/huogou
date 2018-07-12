<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_limits".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $private_letter
 * @property integer $position
 * @property integer $friend_search
 * @property integer $ucenter_buylist
 * @property integer $buylist_number
 * @property integer $ucenter_orderlist
 * @property integer $orderlist_number
 * @property integer $ucenter_sharelist
 * @property integer $sharelist_number
 * @property string $created_at
 * @property string $updated_at
 */
class UserLimit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_limits';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'private_letter', 'position', 'friend_search', 'ucenter_buylist', 'buylist_number', 'ucenter_orderlist', 'orderlist_number', 'ucenter_sharelist', 'sharelist_number', 'created_at', 'updated_at'], 'integer'],
            [['created_at', 'updated_at'], 'required'],
            [['user_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'private_letter' => 'Private Letter',
            'position' => 'Position',
            'friend_search' => 'Friend Search',
            'ucenter_buylist' => 'Ucenter Buylist',
            'buylist_number' => 'Buylist Number',
            'ucenter_orderlist' => 'Ucenter Orderlist',
            'orderlist_number' => 'Orderlist Number',
            'ucenter_sharelist' => 'Ucenter Sharelist',
            'sharelist_number' => 'Sharelist Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function editLimit($model, $params)
    {
        $model->private_letter = $params['private_letter'];
        $model->position = $params['position'];
        $model->friend_search = $params['friend_search'];
        $model->ucenter_buylist = $params['ucenter_buylist'];
        $model->ucenter_orderlist = $params['ucenter_orderlist'];
        $model->ucenter_sharelist = $params['ucenter_sharelist'];
        $model->buylist_number = 0;
        $model->orderlist_number = 0;
        $model->sharelist_number = 0;


        if (isset($params['buylist_number']) && $params['buylist_number'] == 1) {
            $model->buylist_number = $params['buylist_num'];
        }

        if (isset($params['orderlist_number']) && $params['orderlist_number'] == 1) {
            $model->orderlist_number = $params['orderlist_num'];
        }

        if (isset($params['sharelist_number']) && $params['sharelist_number'] == 1) {
            $model->sharelist_number = $params['sharelist_num'];
        }



        if (!isset($model->id)) {
            $model->user_id = Yii::$app->user->id;
            $model->created_at = time();
            $model->updated_at = 0;
        } else {
            $model->updated_at = time();
        }

        if (!$model->save()) {
            return false;
        }

        return true;
    }
}
