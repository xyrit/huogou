<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/19
 * Time: 上午10:01
 */
namespace app\helpers;

class Html extends \yii\helpers\Html
{

    /**
     * Generates a tag that contains the first validation error of the specified model attribute.
     * Note that even if there is no validation error, this method will still return an empty error tag.
     * @param Model $model the model object
     * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
     * about attribute expression.
     * @param array $options the tag options in terms of name-value pairs. The values will be HTML-encoded
     * using [[encode()]]. If a value is null, the corresponding attribute will not be rendered.
     *
     * The following options are specially handled:
     *
     * - tag: this specifies the tag name. If not set, "div" will be used.
     * - encode: boolean, if set to false then the error message won't be encoded.
     *
     * See [[renderTagAttributes()]] for details on how attributes are being rendered.
     *
     * @return string the generated label tag
     */
    public static function error($model, $attribute, $options = [])
    {
        $attribute = static::getAttributeName($attribute);
        $error = $model->getFirstError($attribute);
        if (!$error) {
            return '';
        }
        $tag = isset($options['tag']) ? $options['tag'] : 'div';
        $encode = !isset($options['encode']) || $options['encode'] !== false;
        unset($options['tag'], $options['encode']);
        return Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
    }

}