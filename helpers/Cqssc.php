<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 16/5/18
 * Time: 17:35
 */
namespace app\helpers;


class Cqssc
{
    public $uid = '398134';
    public $name = 'cqssc';
    public $token = '2620affc55581316766daf3af6e4617d243e8f28';

    /**
     * @param $expect
     * @param int $num
     * @param string $order
     * @return array|string
     */
    public function getExpectNum($expect, $num = 120, $order = 'desc')
    {
        $url = 'http://api.caipiaokong.com/lottery/?';
        $date = substr($expect, 0, 8);
        $qi = substr($expect, -3);
        if ($qi==120) {
            $date = date('Ymd',strtotime($date) + 3600*24);
        }
        $queryData = [
            'name' => $this->name,
            'uid' => $this->uid,
            'token' => $this->token,
            'format' => 'json',
            'num' => $num,
            'date' => $date,
        ];
        if ($order == 'asc') {
            $queryData['order'] = 'order';
        }
        $url .= http_build_query($queryData);

        $expectNum = '';
        $waitTime = 10;
        for($i=0;$i<3;$i++) {
            $result = @file_get_contents($url);
            if (!$result) {
                sleep($waitTime);
                continue;
            }
            $json = json_decode($result, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                sleep($waitTime);
                continue;
            }
            if (!empty($json['status']['code'])) {
                @file_put_contents('/tmp/lottery.draw.log',$expect.'--'.print_r($json,true).'---'.date('Y-m-d H:i:s'),FILE_APPEND);
                sleep($waitTime);
                continue;
            } else {
                break;
            }
        }

        if (empty($json[$expect])) {
            return $expectNum;
        }
        $data = $json[$expect];
        if (empty($data['number'])) {
            return $expectNum;
        }
        $expectNum = explode(',', $data['number']);
        $expectNum = implode('', $expectNum);
        return $expectNum;
    }

}