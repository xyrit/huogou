<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/26
 * Time: 下午5:01
 */

namespace app\helpers;

class DateFormat
{

    public static function microDate($time)
    {
        if (!$time) return date('Y-m-d H:i:s');
        $arr = explode('.', $time);
        if (isset($arr[1])) {
            return date('Y-m-d H:i:s', $arr[0]) . '.' . substr($arr[1], 0, 3);
        } else {
            return date('Y-m-d H:i:s', $arr[0]);
        }
    }

    /**
     * 晒单列表时间格式话
     * @param int $time
     */
    public static function formatTime($time)
    {
        $atime = strtotime('today');

        if (date("Y", $time) == date("Y")) {//今年
            if ($time > $atime) { //今天
                if (time() - $time <= 0 ) {//多少秒之前
                    $createTime =  '1秒前';
                }elseif (time() - $time <= 60 ) {//多少秒之前
                    $createTime =  ceil(time() - $time). '秒前';
                }elseif (time() - $time <= 3599) {//多少分钟之前
                    $createTime = intval((time() - $time) / 60) . '分钟前';
                } else {
                    $createTime = '今天 ' . date('H:i', $time);
                }
            } elseif ($time >= $atime - 86400) { //昨天
                $createTime = '昨天 ' . date('H:i', $time);
            } else {
                $createTime = date('m', $time) . '月' . date('d', $time) . '日 ' . date('H:i', $time);
            }
        } else {
            $createTime = date('Y-m-d H:i', $time);
        }

        return $createTime;
    }

    /**
     * 个人主页时间格式
     **/
    public static function userTime($time)
    {
        $atime = strtotime('today');

        $createTime = [];
        if (date("Y", $time) == date("Y")) {//今年
            if ($time > $atime) { //今天
                if (time() - $time <= 3599) {//多少分钟之前
                    $createTime['first'] = intval((time() - $time) / 60) . '分钟前';
                } else {
                    $createTime['first'] = '今天 ' . date('H:i', $time);
                }
            } elseif ($time >= $atime - 86400) { //昨天
                $createTime['first'] = '昨天 ' . date('H:i', $time);
            } else {
                $createTime['first'] = date('m', $time) . '月' . date('d', $time) . '日 ';
                $createTime['second'] = date('H:i', $time);
            }
        } else {
            $createTime['first'] = date('Y', $time).'年';
            $createTime['second'] = date('m', $time) . '月' . date('d', $time) . '日 ';
            $createTime['third'] = date('H:i', $time);
        }

        return $createTime;
    }

    public static function formatConditionTime($region)
    {
        switch ($region) {
            case 1:  //今天
                $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                break;
            case 2:  //本周
                $startTime = mktime(0, 0 , 0, date('m'), date('d')-date('w')+1, date('Y'));
                break;
            case 3:  //本月
                $startTime = mktime(0, 0 , 0, date('m'), 1, date('Y'));
                break;
            case 4:  //最近三个月
                $startTime = strtotime('-3 month');
                break;
        }
        $endTime = time();

        return array($startTime, $endTime);
    }

    public static function rangeTime($type)
    {
        switch ($type) {
            case 'day':  //今天
                $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $endTime = $startTime + 3600*24;
                break;
            case 'yesterday':  //昨天
                $startTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
                $endTime = $startTime + 3600*24;
                break;
            case 'week':  //本周
                $startTime = mktime(0, 0 , 0, date('m'), date('d')-date('w')+1, date('Y'));
                $endTime = $startTime + 3600*24*7;
                break;
            case 'lastWeek':  //上周
                $startTime = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"));
                $endTime = $startTime + 3600*24*7;
                break;
            case 'month':  //本月
                $startTime = mktime(0, 0 , 0, date('m'), 1, date('Y'));
                $endTime = mktime(0, 0 , 0, date('m')+1, 1, date('Y'));
                break;
            case 'lastMonth':  //上月
                $startTime = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                $endTime = mktime(0, 0 , 0,date("m"),1,date("Y"));
                break;
        }

        return array($startTime, $endTime);
    }


}