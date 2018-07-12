<?php

namespace app\models;

use app\helpers\Code;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "invite_link".
 *
 * @property integer $id
 * @property string $code
 * @property integer $user_id
 */
class InviteLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_link';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['code'], 'string', 'max' => 7],
            [['code'], 'unique'],
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
            'code' => 'Code',
            'user_id' => 'User ID',
        ];
    }

    public static function getInviteLink($uid)
    {
        $user = User::find()->select('id')->where(['id'=>$uid])->one();
        if (!$user) {
            return Url::to(['/invite/link', 'code'=>Code::generateShortCode($uid)]);
        }
        $model = static::findOne(['user_id'=>$uid]);
        if ($model) {
            return Url::to(['/invite/link', 'code'=>$model->code]);
        } else {
            $code = Code::generateShortCode($uid);
            $code = $code[0];
            $inviteLink = new static();
            $inviteLink->user_id = $uid;
            $inviteLink->code = $code;
            $inviteLink->save(false);
            return Url::to(['/invite/link', 'code'=>$code]);
        }
    }
}
