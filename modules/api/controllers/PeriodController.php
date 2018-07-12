<?php
/**
 * User: hechen
 * Date: 15/9/29
 * Time: 下午4:27
 */
namespace app\modules\api\controllers;

use app\models\Honour;
use app\models\LotteryComputeDistribution;
use app\models\PaymentOrderItemDistribution;
use app\models\ShareTopic;
use app\models\UserInfo;
use app\services\Period;
use Yii;
use app\services\Product;
use app\helpers\DateFormat;
use app\services\User;
use yii\helpers\ArrayHelper;
use app\models\Period as PeriodModel;
use app\models\CurrentPeriod;
use app\models\LotteryCompute;
use app\services\Category;
use app\models\FollowProduct;
use app\models\PeriodBuylistDistribution;

class PeriodController extends BaseController
{
    /**
     * 期数列表
     * @return [type] [description]
     */
    public function actionList()
    {
        $request = Yii::$app->request;
        $catId = $request->get('cid', 0);
        $page = $request->get('page', 1);
        $isRevealed = $request->get('isRevealed', 'all');
        $pageSize = $request->get('perpage', 20);
        $result = Period::getList($catId, $isRevealed, $page, $pageSize);
        return $result;
    }

    /** 获取某时间之后的正在揭晓的商品
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionGetStartRaffleList()
    {
        $request = Yii::$app->request;
        $time = $request->get('time', microtime(true));
        return Period::getStartRaffleList($time);
    }

    //最新揭晓商品
    public function actionPublishGoods()
    {
        $request = Yii::$app->request;
        if($request->isGet){
            $isRevealed = $request->get('isRevealed');
            $limit = $request->get('perpage');
            $page = $request->get('page');
            $isLimit = $request->get('isLimit', 'all');

            $limit = $limit > 50 ? 50 : $limit;
            $data = Period::getList(0, $isRevealed, $page, $limit, $isLimit);
            return $data;
        }
    }

    /**
     * 本期详情
     * @return [type] [description]
     */
    public function actionInfo()
    {
        $pid = Yii::$app->request->get('id');
        $version=Yii::$app->request->get('version');

        $periodInfo = Period::info($pid);
        if($version && version_compare($version,'2.0.3','>=')){
            if(!$periodInfo){
                return ['code'=>'101','message'=>'不存在该期数'];
            }
        }
        $currentPeriod = Product::curPeriodInfo($periodInfo['goods_id']);
        $periodInfo['current_number'] = $currentPeriod['period_number'];
        $periodInfo['current_no'] = $currentPeriod['period_no'];
        $catInfo = Category::getList($periodInfo['goods_catid']);
        $catNav = '';
        if (isset($catInfo['parent']) && count($catInfo['parent']) > 0) {
            foreach ($catInfo['parent'] as $key => $value) {
                $catNav .= '<i></i><a href="/list-'.$value['id'].'-0.html">'.$value['name'].'</a>';
            }
            $catNav .= '<i></i><a href="/list-'.$catInfo['id'].'-0.html">'.$catInfo['name'].'</a>';
        }else{
            $catNav .= '<i></i><a href="/list-'.$catInfo['id'].'-0.html">'.$catInfo['name'].'</a>';
        }
        $periodInfo['goods_catNav'] = $catNav;

        $prePeriod = PeriodModel::find()->select('id')->where(['period_number'=>($periodInfo['period_number']-1),'product_id'=>$periodInfo['goods_id']])->asArray()->one();
        $nextPeriod = PeriodModel::find()->select('id')->where(['period_number'=>($periodInfo['period_number']+1),'product_id'=>$periodInfo['goods_id']])->asArray()->one();

        $hasBuy = 0;
        if ($this->userId) {
            $followed = FollowProduct::find()->where(['user_id'=>$this->userId,'product_id'=>$periodInfo['goods_id']])->asArray()->one();
            $hasBuy = Period::getUserHasBuyCount($this->userId,$periodInfo['period_id']);
        }

        $periodInfo['prePeriodId'] = $prePeriod ? $prePeriod['id'] : '0';
        $periodInfo['nextPeriodId'] = $nextPeriod ? $nextPeriod['id'] : '0';
        $periodInfo['share_num'] = ShareTopic::find()->where(['product_id' => $periodInfo['goods_id'], 'is_pass' => 1])->count();

        //app 期数开始时间改为第一条购买时间;
        $tableId = $periodInfo['table_id'];
        $periodFirstBuy = PeriodBuylistDistribution::findByTableId($tableId)->where(['period_id'=>$pid])->orderBy('buy_time asc')->one();
        $periodInfo['start_time'] = date('Y-m-d H:i:s',$periodFirstBuy['buy_time']);

        unset($periodInfo['table_id']);

        $userInfo = [
            'hasBuy'=>$hasBuy > 0 ? $hasBuy : 0,
            'followed'=>empty($followed) ? false : true ,
            'logined'=>empty($this->userId) ? false : true,
        ];
        return array('periodInfo'=>$periodInfo,'userInfo'=>$userInfo);
    }

    /**
     * 购买列表
     * @return [type] [description]
     */
    public function actionBuylist(){
        $request = Yii::$app->request;
        $page = $request->get('page',1);
        $id = $request->get('id');

        $perpage = $request->get('perpage') ? $request->get('perpage') : 10;

        $perpage = $perpage > 50 ? 50 : $perpage;
        $buyList = Period::buyList($id,$page,$perpage);
        $buyList['page'] = $page;

        return $buyList;
    }

    /**
     * 我的购买记录
     * @return [type] [description]
     */
    public function actionMycodes(){
        $pid = Yii::$app->request->get("pid");
        if ($this->userId) {
            $codes = Period::getCodeByUser($this->userId,$pid);
            $total= 0;
            $partCodes = array();
            foreach ($codes as $key => $value) {
                $_t = count(explode(',',$value['codes']));
                $o = $total;
                $total += $_t;
//                if ($total >= 100) {
//                    $value['codes'] = implode(',',array_slice(explode(',',$value['codes']),0,100-$o));
//                    $partCodes[$key] = $value;
//                    break;
//                }
                $partCodes[$key] = $value;
            }

            $period = \app\models\Period::find()->select('lucky_code')->where(['id'=>$pid])->andWhere(['>','user_id',0])->one();
            $luckyCode = $period ? $period['lucky_code'] : '';
            return array('code'=>'100','data'=>$partCodes,'codes'=>$total,'lucky_code'=>$luckyCode);
        }
        return array('code'=>'0');
    }


    /**
     * 他人的购买记录
     * @return [type] [description]
     */
    public function actionHecodes(){
        $home_id=Yii::$app->request->get("hid");
        $pid = Yii::$app->request->get("pid");
        $uid=\app\models\User::find()->where(['home_id' => $home_id])->asArray()->one();

        if ($uid) {
            $codes = Period::getCodeByUser($uid['id'],$pid);
            $total= 0;
            $partCodes = array();
            foreach ($codes as $key => $value) {
                $_t = count(explode(',',$value['codes']));
                $o = $total;
                $total += $_t;
//                if ($total >= 100) {
//                    $value['codes'] = implode(',',array_slice(explode(',',$value['codes']),0,100-$o));
//                    $partCodes[$key] = $value;
//                    break;
//                }
                $partCodes[$key] = $value;
            }

            $period = \app\models\Period::find()->select('lucky_code')->where(['id'=>$pid])->andWhere(['>','user_id',0])->one();
            $luckyCode = $period ? $period['lucky_code'] : '';
            return array('code'=>'100','data'=>$partCodes,'codes'=>$total,'lucky_code'=>$luckyCode);
        }
        return array('code'=>'0');
    }



    /**
     * 计算结果
     * @return [type] [description]
     */
    public function actionCompute(){
        $pid = Yii::$app->request->get('pid');
        $period = \app\models\Period::find()->where(['id' => $pid])->asArray()->one();
        $data = LotteryComputeDistribution::findByTableId($period['table_id'])->where(['period_id' => $pid])->asArray()->one();
        if ($data) {
            $computeData = unserialize($data['data']);
        } else {
            $lastBuy = PaymentOrderItemDistribution::lastBuy($period['end_time'], 50);
            $computeData = [];
            foreach ($lastBuy as $k => $v) {
                $time = explode(".", $v['item_buy_time']);
                $lastTime = isset($time[1]) ? substr($time[1], 0, 3) : '0';
                $timeData = date("His", $time[0]) . str_pad($lastTime, 3, 0, STR_PAD_RIGHT);
                $computeData[] = array(
                    'buy_time' => $v['item_buy_time'],
                    'data' => $timeData,
                    'user_id' => $v['user_id'],
                    'buy_num' => $v['nums'],
                    'product_id' => $v['product_id'],
                    'period_id' => $v['period_id'],
                    'period_number' => $v['period_number']
                );
            }
            if ($period['result_time'] - time() >= 63 || time() > $period['result_time']) {
                $shishiQishu = \app\services\Period::shishiQishu($period['end_time']);
                $ld = new LotteryComputeDistribution($period['table_id']);
                $ld->period_id = $period['id'];
                $ld->data = serialize($computeData);
                $ld->expect = $shishiQishu;
                $l = $ld->save(false);
            }
        }

        $products = $users = array();
        foreach ($computeData as $key => $value) {
            $products[] = $value['product_id'];
            $periodId[] = $value['period_id'];
            $users[] = $value['user_id'];
        }

        $products = Product::info($products);
        $usersInfo = User::baseInfo($users);

        $total = 0;

        foreach ($computeData as $key => &$value) {
            $value['buy_time'] = DateFormat::microDate($value['buy_time']);
            $value['home_id'] = $usersInfo[$value['user_id']]['home_id'];
            $value['product_name'] = $products[$value['product_id']]['name'];
            $value['username'] = $usersInfo[$value['user_id']]['username'];
            $total += $value['data'];
        }
        if ($data && ($period['result_time'] <= time()) && $period['user_id'] > 0) {
            $shishiData = [
                'expect' => $data['expect'],
                'shishi_num' => $data['shishi_num'],
            ];
            $luckyCode = $period['lucky_code'];
        } else {
            $shishiData = [
                'expect' => Period::shishiQishu($period['end_time']),
                'shishi_num' => '',
            ];
            $luckyCode = 0;
        }
        return array(
            'total' => $total,
            'list' => $computeData,
            'price' => $period['price'],
            'endTime' => DateFormat::microDate($period['end_time']),
            'shishiData' => $shishiData,
            'luckyCode' => $luckyCode
        );
    }

    /**
     * 当期所有码
     * @return [type] [description]
     */
    public function actionAllCodes(){
        $periodId = Yii::$app->request->get('pid');
        return Period::getLotteryCodes($periodId);
    }


    /**
     * 已购买
     * @return [type] [description]
     */
    public function actionHasbuy(){
        $pid = Yii::$app->request->get('id');
        if (!$this->userId) {
            return array('buyNum'=>0);
        }
        $buyNum = Period::getUserHasBuyCount($this->userId,$pid);
        return array('buyNum'=>$buyNum);
    }

    public function actionGetuserbuycodesbybuyid()
    {
        $request = Yii::$app->request;
        $periodId = $request->get('periodid');
        $buyId = $request->get('buyid');
        $codes = Period::getUserBuyCodesByBuyId($periodId, $buyId);
        return ['codes'=>$codes];
    }

    /**
     * 获取最新购买
     * @return [type] [description]
     */
    public function actionGetNewBuyList(){
        $time = Yii::$app->request->get('lasttime');
        $pid = Yii::$app->request->get('pid');

        $list = Period::getNewBuyList($time,$pid);
        $users = array();
        foreach ($list as $key => $value) {
            $users[] = $value['user_id'];
        }

        $userInfo = User::baseInfo($users);
        $data = array();

        foreach ($list as $key => $value) {
            $data[$key]['home_id'] = $userInfo[$value['user_id']]['home_id'];
            $data[$key]['avatar'] = $userInfo[$value['user_id']]['avatar'];
            $data[$key]['username'] = $userInfo[$value['user_id']]['username'];
            $data[$key]['buy_num'] = $value['buy_num'];
            $data[$key]['buy_time'] = $value['buy_time'];
        }

        return array('list'=>$data);
    }

    /**
     * 根据商品id和商品期数获取站点期数
     * @return [type] [description]
     */
    public function actionGetPeriodid(){
        $product_id = Yii::$app->request->get("pid");
        $period_number = Yii::$app->request->get("pnum");
        $periodInfo = CurrentPeriod::find()->select('id')->where(['period_number'=>$period_number,'product_id'=>$product_id])->asArray()->one();
        if (!$periodInfo) {
            $periodInfo = PeriodModel::find()->select('id')->where(['period_number'=>$period_number,'product_id'=>$product_id])->asArray()->one();
        }
        $period_id = $periodInfo['id'];
        return array('period_id'=>$period_id);
    }

    public function actionState(){
        $pid = Yii::$app->request->get('pid');
        $periodInfo = PeriodModel::find()->where(['id'=>$pid])->asArray()->one();
        if (!$periodInfo) {
            return array('code'=>100,'result'=>'underway');
        }else{
            if ($periodInfo['user_id'] > 0 && $periodInfo['lucky_code'] > 0 && $periodInfo['exciting_time'] > 0) {
                return array('code'=>100,'result'=>'announce');
            }else{
                return array('code'=>100,'result'=>'countdown');
            }
        }
    }

    //当期荣誉榜
    public function actionReward()
    {
        $pid = Yii::$app->request->get('pid');
        $periodInfo = PeriodModel::findOne($pid);

        if($periodInfo){
            $honourModel = Honour::find()->where(['period'=>$periodInfo['id']])->asArray()->one();
            if(!$honourModel){
                $model = new Honour();
                $first = PeriodBuylistDistribution::findByTableId($periodInfo['table_id'])->select('user_id,buy_num')->where(['period_id'=>$periodInfo['id']])->orderBy('id asc')->one();
                $last = PeriodBuylistDistribution::findByTableId($periodInfo['table_id'])->select('user_id,buy_num')->where(['period_id'=>$periodInfo['id']])->orderBy('id desc')->one();
                $rich = PeriodBuylistDistribution::findByTableId($periodInfo['table_id'])->select('user_id,buy_num')->where(['period_id'=>$periodInfo['id']])->orderBy('buy_num desc, buy_time asc ')->one();
                $model->buynum = $rich['buy_num'];
                $model->period = $periodInfo['id'];
                $model->end_userid = $last['user_id'];
                $model->first_userid = $first['user_id'];
                $model->created_at = time();
                $model->rich_userid = $rich['user_id'];
                if($model->save()){
                    $honourModel = Honour::find()->where(['period'=>$periodInfo['id']])->asArray()->one();
                }
            }

            $arr[0] = $honourModel['rich_userid'];
            $arr[1] = $honourModel['first_userid'];
            $arr[2] = $honourModel['end_userid'];
            $userInfo = User::baseInfo($arr);

            $return['rich_avatar'] = $userInfo[$honourModel['rich_userid']]['avatar'];
            $return['rich_username'] = $userInfo[$honourModel['rich_userid']]['username'];
            $return['buy_num'] = $honourModel['buynum'];
            $return['first_avatar'] = $userInfo[$honourModel['first_userid']]['avatar'];
            $return['first_username'] = $userInfo[$honourModel['first_userid']]['username'];
            $return['end_avatar'] = $userInfo[$honourModel['end_userid']]['avatar'];
            $return['end_username'] = $userInfo[$honourModel['end_userid']]['username'];

            return $return;
        }else{
            return ['error'=>1, 'message'=>'不存在该期数'];
        }
    }
}
