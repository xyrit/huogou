<?php

namespace app\helpers;
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/30
 * Time: 下午7:43
 */
class TimeHelper
{

    public static function getCurWeekStartEnd()
    {
        //当前日期
        $sdefaultDate = date("Y-m-d");
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first = 1;
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        //本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));

        return ['start'=>$week_start, 'end'=>$week_end];
    }

    public static function getCurMonthStartEnd()
    {
        $firstday = date("Y-m-01");
        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
        return ['start'=>$firstday, 'end'=>$lastday];
    }


}