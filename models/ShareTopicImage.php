<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "share_topic_images".
 *
 * @property string $id
 * @property string $share_topic_id
 * @property string $basename
 */
class ShareTopicImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'share_topic_images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['share_topic_id', 'basename'], 'required'],
            [['share_topic_id'], 'integer'],
            [['basename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'share_topic_id' => 'Share Topic ID',
            'basename' => 'Basename',
        ];
    }
    
    public static function getImagesByShareTopicId($shareTopicId, $limit = 0)
    {
        $query = ShareTopicImage::find()->where(['share_topic_id' => $shareTopicId, 'is_show' => 1]);
        if ($limit != 0) {
            $query->limit($limit);
        }
        $shareTopicImage = $query->orderBy('order asc')->all();
        
        if ($shareTopicImage) {
            $pictures = ArrayHelper::getColumn($shareTopicImage, 'basename');
        } else {
            $pictures = [];
        }
        
        return $pictures;
    }
    
    public static function updateImage($shareTopicId, $pictures)
    {
        $shareTopicImage = ShareTopicImage::findAll(['share_topic_id' => $shareTopicId]);
        $oldPics = ArrayHelper::getColumn($shareTopicImage, 'basename');
        $delPics = array_diff($oldPics, $pictures);
        $insertPics = array_diff($pictures, $oldPics);
        if (empty($delPics)) {
            // 插入新图片
            foreach ($insertPics as $key => $pic) {
                $shareTopicImage = new ShareTopicImage();

                $shareTopicImage->share_topic_id = $shareTopicId;
                $shareTopicImage->basename = $pic;
                if ($key == 0) {
                    $shareTopicImage->main = 1;
                }
                if (!$shareTopicImage->save()) {
                    return false;
                }
            }
        } else {
            // 删除数据库不要的图片
            if (ShareTopicImage::deleteAll(['share_topic_id' => $shareTopicId, 'basename' => $delPics])) {
                // 删除服务器上不要的图片
                foreach ($delPics as $pic) {
                    Image::deleteShareInfoImage($pic);
                }
                // 插入新图片
                foreach ($insertPics as $key => $pic) {
                    $shareTopicImage = new ShareTopicImage();

                    $shareTopicImage->share_topic_id = $shareTopicId;
                    $shareTopicImage->basename = $pic;
                    if ($key == 0) {
                        $shareTopicImage->main = 1;
                    }
                    if (!$shareTopicImage->save()) {
                        return false;
                    }
                }

                ShareTopicImage::updateAll(['main' => 0], ['share_topic_id' => $shareTopicId]);
                ShareTopicImage::updateAll(['main' => 1], ['share_topic_id' => $shareTopicId, 'basename' => $pictures[0]]);
            }
        }

        return true;
    }
}
