<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/21
 * Time: 上午10:10
 */

namespace app\widgets;

class BackendLinkPager extends \yii\widgets\LinkPager
{
    /**
     * @var array HTML attributes for the pager container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'am-pagination'];

    /**
     * @var string the CSS class for the active (currently selected) page button.
     */
    public $activePageCssClass = 'am-active';
    /**
     * @var string the CSS class for the disabled page buttons.
     */
    public $disabledPageCssClass = 'am-disabled';

    public $firstPageLabel = '第一页';
    
    public $lastPageLabel = '最后一页';

}