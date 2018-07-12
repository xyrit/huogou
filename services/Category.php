<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/12
 * Time: 下午6:37
 */

namespace app\services;

use app\models\ProductCategory;
use yii\helpers\ArrayHelper;

class Category
{

    public static function getList($catId = 0)
    {
        if ($catId) {
        	$catInfo = ProductCategory::getInfoById($catId);
            if ($catInfo['level'] == 1) {
                $son = ProductCategory::children($catId);
                $catInfo['son'] = $son;    
            }else{
                $parent = ProductCategory::parents($catId);
                $catInfo['parent'] = $parent;
            }
            return $catInfo;
        } else {
            $list = ProductCategory::firstLevel();
            // $list = array();
            // foreach ($_list as $key => $value) {
            // 	$list[$value['id']] = $value;
            // }
            return $list;
        }
    }

    public static function getCateList()
    {
        $all = ProductCategory::find()->asArray()->all();
        $return = [];
        $list = self::getCategoryList();
        foreach($all as $key => $val){
            $name = '';
            if($val['top_id'] != 0 && $val['top_id'] != $val['parent_id']){
                $name .= $list[$val['top_id']].' > ';
            }
            if($val['parent_id'] != 0){
                $name .= $list[$val['parent_id']].' > ';
            }
            $name .= $val['name'];
            $return[$key]['id'] = $val['id'];
            $return[$key]['name'] = $name;
        }
        return ArrayHelper::map($return, 'id', 'name');
    }

    public static function getCategoryList()
    {
        $all = ProductCategory::find()->all();
        return ArrayHelper::map($all, 'id', 'name');
    }

    public static function getCatName($catId = 0, $return = 0)
    {
        $catInfo = Category::getList($catId);
        $catNav = '';
        if (isset($catInfo['parent']) && count($catInfo['parent']) > 0) {
            foreach ($catInfo['parent'] as $key => $value) {
                $catNav .= $value['name'].">";
            }
            $catNav .= $catInfo['name'];
        }else{
            $catNav .= $catInfo['name'];
        }
        if($return == 0){
            $arr = explode('>', $catNav);
            return $arr;
        }else{
            return $catNav;
        }

    }

    public static function getCatByCid($cid){
        return ProductCategory::getInfoById($cid);
    }
}