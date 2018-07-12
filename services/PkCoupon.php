<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/7/15
 * Time: 上午10:18
 */
namespace app\services;

use app\models\UserCoupons;
use app\models\Coupon as CouponModel;
use app\models\PkCurrentPeriod as PkCurrentPeriodModel;

class PkCoupon
{

    /**
     * 获取用户有效优惠券并更新已过期
     * @param  int $uid 用户ID
     * @return [type]      [description]
     */
    public static function getUserValidList($uid, $type = 'pk_use', $periodId, $buyNum)
    {
        if (!$uid) {
            return ['code' => 201, 'msg' => '未登录'];
        }

        $validCoupon = UserCoupons::getValidCoupons($uid);
        $validCouponInfo = self::getCouponInfo($validCoupon);

        $validCouponInfo = self::_checkCoupons($uid, $type, $validCouponInfo, $periodId, $buyNum);
        return $validCouponInfo;
    }

    /**
     * 获取优惠券信息
     * @param  array $userCoupons 用户优惠券列表ID
     * @return [type]              [description]
     */
    public static function getCouponInfo($userCoupons)
    {
        $couponId = '';
        foreach ($userCoupons as $key => $value) {
            $couponId[] = $value['coupon_id'];
        }

        $couponInfo = CouponModel::getInfo($couponId);

        foreach ($userCoupons as $key => &$value) {
            $value['info'] = $couponInfo[$value['coupon_id']];
        }

        return $userCoupons;
    }

    /**
     * 检测优惠券
     * @param  array $coupons 优惠券码
     * @return [type]              [description]
     */
    public static function checkCoupons($uid, $coupons, $periodId, $buyNum)
    {
        $userCoupon = UserCoupons::getCodeInfo($uid, array_keys($coupons));
        $valid = [];
        foreach ($userCoupon as $key => $value) {
            if ($value['code'] != $coupons[$value['id']] || $value['status'] != 0) {
                unset($userCoupon[$key]);
            }
        }
        $couponInfo = self::getCouponInfo($userCoupon);

        return self::_checkCoupons($uid, 'pk_use', $couponInfo, $periodId, $buyNum);
    }

    /**
     * 检测优惠券
     * @param  array $coupons 优惠券信息
     * @return [type]          [description]
     */
    private static function _checkCoupons($uid, $type, $coupons, $periodId, $buyNum)
    {
        $invalidList = '';
        $time = time();
        if ($type == 'pk_use') {
            $period = PkCurrentPeriodModel::find()->where(['id' => $periodId])->asArray()->one();
            if (!$period) {
                return [];
            }
            $total = ceil($period['price']/2) * $buyNum;
            $productId = $period['product_id'];
            if ($total == 0) {
                return [];
            }
        }
        foreach ($coupons as $key => $value) {
            //是否过期
            if ($value['info']['valid_type'] == 1) {
                if ($time < $value['info']['start_time'] || $time > $value['info']['end_time']) {
                    $invalidList[] = $value['id'];
                    unset($coupons[$key]);
                }
            } else if ($value['info']['valid_type'] == '2') {
                if (($value['info']['valid'] + $value['receive_time']) < $time) {
                    $invalidList[] = $value['id'];
                    unset($coupons[$key]);
                }
            }
            if ($type == 'pk_use' && isset($coupons[$key])) {
                if ($value['info']['type'] == 3) {
                    unset($coupons[$key]);
                    continue;
                }
                $condition = json_decode($value['info']['condition'], true);
                $amount = json_decode($value['info']['amount'], true);
                $range = explode(',', $condition['range']);
                if ($value['info']['type'] == 1 && $total < $amount['money']) {
                    unset($coupons[$key]);
                    continue;
                }
                if (in_array(1, $range)) {
                    if ($condition['need'] >= 0 && $condition['need'] <= $total) {
                        if ($value['info']['type'] == 1) {
                            $coupons[$key]['deduction'] = $amount['money'];
                        } else if ($value['info']['type'] == 2) {
                            $coupons[$key]['deduction'] = intval($total * (1 - $amount['discount'] / 100));
                        }
                    } else {
                        unset($coupons[$key]);
                    }
                } else {
                    $coupons[$key]['deduction'] = 0;
                    $invalid = 1;
                    //指定pk商品
                    if (in_array(5, $range)) {
                        if ($condition['need'] >= 0) {
                            if (in_array($productId,explode(',',$condition['pk_products']))) {
                                if ($total > 0 && $condition['need'] <= $total) {
                                    $invalid = 0;
                                    if ($value['info']['type'] == 1) {
                                        $coupons[$key]['deduction'] = $amount['money'];
                                    } else if ($value['info']['type'] == 2) {
                                        $coupons[$key]['deduction'] += intval($total * (1 - $amount['discount'] / 100));
                                    }
                                } else {
                                    $invalid = 1;
                                }
                            }

                        } else {
                            $invalid = 1;
                        }
                    }
                    if ($invalid) {
                        unset($coupons[$key]);
                    }
                }
            }
        }

        if ($invalidList) {
            UserCoupons::updateAll($uid,['status' => 3], ['in', 'id', $invalidList]);
        }

        return $coupons;
    }


}