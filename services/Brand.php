<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/12
 * Time: 下午6:38
 */
namespace app\services;

use app\models\CategoryBrand;
use app\models\ProductCategory;

class Brand
{

    public static function getList($catId = 0)
    {
        if ($catId) {
            return CategoryBrand::find()->where(['cat_id'=>$catId])->asArray()->all();
        } else {
            $list = CategoryBrand::find()->select('category_brand.id,brands.id as bid,brands.name,brands.alias,category_brand.cat_id')->leftJoin('brands','category_brand.brand_id=brands.id')->leftJoin('products', 'category_brand.brand_id = products.brand_id and category_brand.cat_id = products.cat_id')->where(['>','product_num','0'])->andWhere('marketable = 1')->asArray()->all();
            $_category = ProductCategory::all();
            $data = array();

            foreach ($_category as $key => $value) {
                $category[$value['id']] = $value;
            }
            foreach ($category as $key => $value) {
            	foreach ($list as $k => $v) {
            		if ($value['id'] == $v['cat_id']) {
            			if ($value['level'] == 1) {
            				$list[$k]['cid'] = $value['id'];
                            $data[$value['id']][$v['bid']] = ['name'=>$v['name'],'alias'=>$v['alias']];
            			}else{
                            $level = 0;
                            $cid = $value['parent_id'];
                            while ($level != 1) {
                                $level = $category[$cid]['level'];
                                $list[$k]['cid'] = $cid;
                                if ($level == 1) {
                                    $data[$cid][$v['bid']] = ['name'=>$v['name'],'alias'=>$v['alias']];
                                }
                                $cid = $category[$cid]['parent_id'];
                            }
            			}
            		}
            	}  
            }
            return $data;
        }
    }
}