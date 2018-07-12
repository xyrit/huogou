<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/30
 * Time: 下午1:28
 */

namespace app\services;

use app\models\Cart as CartModel;
use app\models\UserBuylistDistribution;
use app\services\Product;
use app\services\Period;
use app\models\CurrentPeriod;

class Cart
{

    /** 添加购物车
     * @param $userId
     * @param $periodId
     * @param $num
     * @return bool
     */
    public static function add($userId, $productId, $num, $addFromTmp = false)
    {
        if ($num <= 0) {
            return ['code'=>106];//添加购物车商品数量不能为0
        }
        $period = Product::curPeriodInfo($productId);
        if ($period) {

            $num = ceil($num/$period['buy_unit'])*$period['buy_unit'];

            $leftNum = $period['left_num'];
            $limitBuyNum = $period['limit_num'];
            if ($limitBuyNum > 0 && $num > $limitBuyNum) {
                if ($num>1) {
                    $num = $limitBuyNum;
                } else {
                    return ['code'=>105];//购买数量超过限购数量
                }
            }

            $cart = CartModel::findOne(['user_id' => $userId, 'product_id' => $productId]);
            if (!$cart) {
                $num = $leftNum <= $num ? $leftNum : $num;

            } else {
                $num = $cart->nums + $num;
                $num = $leftNum <= $num ? $leftNum : $num;

            }
            if ($limitBuyNum > 0) {
                $userBaseInfo = User::baseInfo($userId);
                $userHomeId = $userBaseInfo['home_id'];
                $userBuyList = UserBuylistDistribution::findByUserHomeId($userHomeId)
                    ->where(['user_id' => $userId, 'period_id' => $period['id']])
                    ->asArray()
                    ->one();
                $userBuyNum = $userBuyList['buy_num'];
                if ($userBuyNum > 0) {
                    if ($limitBuyNum - $userBuyNum <= 0) {
                        if (!$addFromTmp) {
                            return ['code'=>104];//不能再购买限购数量的商品
                        } else {
                            $num = 0;
                        }
                    } elseif ($limitBuyNum - $userBuyNum < $num) {
                        $num = $limitBuyNum - $userBuyNum;
                    }
                }
            }
            if (!$cart) {
                $cart = new CartModel();
                $cart->user_id = $userId;
                $cart->period_id = $period['id'];
                $cart->period_number = $period['period_number'];
                $cart->product_id = $productId;
                $cart->nums = $num;
                $cart->limit_nums = $limitBuyNum;
                $cart->buy_unit = $period['buy_unit'];
            } else {
                $cart->nums = $num;
            }
            if ($num==0) {
                $cart->is_buy = 0;
            }

            $save =  $cart->save();
            return $save ? ['code'=>100] : ['code'=>103];//添加购物车，100=成功，103=失败
        }
        return ['code'=>102];
    }

    /** 删除一个或多个购物车里的商品
     * @param $cartId
     * @return int
     */
    public static function del($cartId)
    {
        return CartModel::deleteAll(['id' => $cartId]);
    }

    /** 删除用户购物车
     * @param $uid
     * @return int
     */
    public static function delByUid($uid)
    {
        return CartModel::deleteAll(['user_id'=>$uid]);
    }

    /** 更新购物车商品数量
     * @param $userId
     * @param $productId
     * @param $num
     * @return bool
     */
    public static function updateNum($userId, $productId, $num)
    {
        $period = Product::curPeriodInfo($productId);
        if ($period) {
            $num = ceil($num/$period['buy_unit'])*$period['buy_unit'];
            $leftNum = $period['left_num'];
            if ($leftNum >= $num) {
                $cart = CartModel::findOne(['user_id' => $userId, 'product_id' => $productId]);
                $hasBuy = Period::getUserHasBuyCount($userId,$period['id']);
                if ($cart) {
                    if ($period['limit_num'] > 0) {
                        $canBuy = 0;
                        if ($period['limit_num']-$hasBuy > 0) {
                            $canBuy = $period['limit_num']-$hasBuy >= $num ? $num : ($period['limit_num']-$hasBuy);
                        }
                        $cart->nums = $num;
                        $cart->save();
                        return array('limit'=>$period['limit_num'],'pid'=>$productId,'canBuy'=>$canBuy);
                    }
                    $cart->nums = $num;
                    return array('canBuy'=>$cart->save());
                }
            }
        }
        return array('canBuy'=>false);
    }

    /** 更新购物车 是否购买状态
     * @param $userId
     * @param $productIds
     * @param $buyStates
     * @return bool
     */
    public static function updateBuyStat($userId, $productIds, $buyStates)
    {
        try {
            $_periods = CurrentPeriod::find()->where(['in','product_id',$productIds])->asArray()->all();
            $_cartInfo = CartModel::find()->where(['user_id'=>$userId])->asArray()->all();
            $periods = $cartInfo = array();
            foreach ($_periods as $key => $value) {
                $periods[$value['product_id']] = $value;
            }
            foreach ($_cartInfo as $key => $value) {
                $cartInfo[$value['product_id']] = $value;
            }
            
            $invalid = 0;
            foreach ($productIds as $key => $productId) {
                if (!isset($periods[$productId]) || ($cartInfo[$productId]['period_id'] != $periods[$productId]['id'])) {
                    $invalid = 1;
                }
                CartModel::updateAll(['is_buy' => $buyStates[$key]], ['user_id' => $userId, 'product_id' => $productId]);
            }
            return array('invalid'=>$invalid);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *  获取某用户购物车
     * @param $userId
     * @param $isBuy 是否购买状态 null=全部状态,0=不购买,1=购买
     * @return array
     */
    public static function info($userId, $update = true, $isBuy = null)
    {
        $query = CartModel::find()
            ->select('carts.*,p.price,p.name,p.picture')
            ->leftJoin('products p', 'carts.product_id=p.id')
            ->where(['carts.user_id' => $userId]);
        if ($isBuy !== null) {
            $isBuy = $isBuy ? 1 : 0;
            $query->andWhere(['carts.is_buy' => $isBuy]);
        }
        $query->orderBy('id asc');
        $carts = $query->asArray()->all();
        foreach ($carts as $key=>&$cart) {
            $periodId = $cart['period_id'];
            $productId = $cart['product_id'];
            $curPeriod = Product::curPeriodInfo($productId);
            if (!$curPeriod) {
                CartModel::deleteAll(['id'=>$cart['id']]);
                unset($carts[$key]);
                continue;
            }

            $cart['old_nums'] = $cart['nums'];

            if ($periodId != $curPeriod['id'] && $update) {
                $cart['period_id'] = $curPeriod['id'];
                $cart['nums'] = ceil($cart['nums']/$curPeriod['buy_unit'])*$curPeriod['buy_unit'];
                CartModel::updateAll([
                    'period_id' => $curPeriod['id'],
                    'period_number'=>$curPeriod['period_number'],
                    'limit_nums'=>$curPeriod['limit_num'],
                    'buy_unit'=>$curPeriod['buy_unit'],
                ], ['id' => $cart['id']]);
            }
            $myLimitNum = $curPeriod['limit_num'];
            if ($curPeriod['limit_num'] > 0) {
                $hasBuy = Period::getUserHasBuyCount($userId,$curPeriod['id']);
                if ($curPeriod['limit_num'] > $hasBuy) {
                    $myLimitNum = $curPeriod['limit_num'] - $hasBuy;
                } else {
                    $myLimitNum = 0;
                }
                $compareNum = $curPeriod['left_num'] > $myLimitNum ? $myLimitNum : $curPeriod['left_num'];
            } else {
                $compareNum = $curPeriod['left_num'];
            }
            if ($cart['nums'] >= $compareNum) {
                $cart['nums'] = $compareNum;
                if ($compareNum==0) {
                    $cart['is_buy'] = 0;
                    $updateCond = ['nums' => $compareNum,'is_buy'=>0];
                } else {
                    $updateCond = ['nums' => $compareNum];
                }
                CartModel::updateAll($updateCond, ['id' => $cart['id']]);
            } else {
                if ($cart['old_nums']!=$cart['nums']) {
                    CartModel::updateAll(['nums'=>$cart['nums']], ['id' => $cart['id']]);
                }
            }
            $cart['old_period_id'] = $periodId;
            $cart['period_number'] = $curPeriod['period_number'];
            $cart['left_num'] = $curPeriod['left_num'];
            $cart['sales_num'] = $curPeriod['sales_num'];
            $cart['limit_num'] = $curPeriod['limit_num'];
            $cart['buy_unit'] = $curPeriod['buy_unit'];
            $cart['price'] = $curPeriod['price'];
            $cart['my_limit_num'] = $myLimitNum;
        }
        CartModel::deleteAll(['user_id'=>$userId,'nums'=>0]);
        return $carts;
    }

    /**
     * 购物车商品数量
     * @param  int $uid 用户ID
     * @return [type]      [description]
     */
    public static function count($uid)
    {
        $count = CartModel::find()->select("count(1) as total")->where(['user_id' => $uid])->asArray()->one();
        return array('count' => ($count['total'] ? $count['total'] : 0));
    }

    /**
     * 临时购物车
     * @param  [type] $cart [description]
     * @return [type]       [description]
     */
    public static function tmpInfo($cart)
    {
        $productIds = array_keys($cart);
        $productInfo = Product::info($productIds);
        $cartInfo = array();
        foreach ($productIds as $key => $value) {
            $cartInfo[$key]['id'] = $key + 1;
            $cartInfo[$key]['product_id'] = $value;
            $cartInfo[$key]['nums'] = $cart[$value];
            $cartInfo[$key]['price'] = $productInfo[$value]['price'];
            $cartInfo[$key]['name'] = $productInfo[$value]['name'];
            $cartInfo[$key]['picture'] = $productInfo[$value]['picture'];
        }
        foreach ($cartInfo as &$cart) {
            $periodId = 0;
            $productId = $cart['product_id'];
            $curPeriod = Product::curPeriodInfo($productId);
            if ($periodId != $curPeriod['id']) {
                $cart['period_id'] = $curPeriod['id'];
            }
            $cart['old_nums'] = $cart['nums'];
            if ($curPeriod['limit_num'] > 0) {
                $compareNum = $curPeriod['left_num'] > $curPeriod['limit_num'] ? $curPeriod['limit_num'] : $curPeriod['left_num'];
            } else {
                $compareNum = $curPeriod['left_num'];
            }
            if ($cart['nums'] > $compareNum) {
                $cart['nums'] = $compareNum;
            }
            $cart['old_period_id'] = $periodId;
            $cart['period_number'] = $curPeriod['period_number'];
            $cart['left_num'] = $curPeriod['left_num'];
            $cart['sales_num'] = $curPeriod['sales_num'];
            $cart['limit_num'] = $curPeriod['limit_num'];
            $cart['buy_unit'] = $curPeriod['buy_unit'];
            $cart['my_limit_num'] = $curPeriod['limit_num'];
            $cart['price'] = $curPeriod['price'];
        }
        return $cartInfo;
    }

    /**
     * 获取用户购物车总价格
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function getCartMoneByUid($uid){
        $total = CartModel::find()->select('sum(nums) as total')->where(['and',"user_id='".$uid."'","is_buy=1"])->asArray()->one();
        return $total['total'];
    }
}