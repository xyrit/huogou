<?php
/**
 * User: hechen
 * Date: 15/9/30
 * Time: 下午5:15
 */
namespace app\modules\api\controllers;

use app\models\CurrentPeriod;
use app\services\Period;
use app\services\Product;
use yii;
use yii\helpers\Json;
use app\services\Cart;
use app\models\User;
use yii\web\Cookie;
use app\helpers\Version;

class CartController extends BaseController
{	
	private $cookies;
	private $isTmp = false;
	private $tmpCart = array();
	public function init(){
		parent::init();
		$this->cookies = \Yii::$app->response->cookies;
		if (!$this->userId || $this->userId == 0) {
			$this->isTmp = true;
		}
		$this->tmpCart = json_decode(Yii::$app->request->cookies->get('_tc','[]'),true) ? : array();
	}
	/**
	 * 加入购物车
	 * @return [type] [description]
	 */
	public function actionAdd(){
		$productId = Yii::$app->request->get('productId','0');
		$num = Yii::$app->request->get('num');
		if (!$productId) {
			$periodId = Yii::$app->request->get('periodid');
			$curPeriod = CurrentPeriod::findOne($periodId);
			if (!$curPeriod) {
				$period = \app\models\Period::find()->where(['id'=>$periodId])->one();
				$productId = !empty($period->product_id) ? $period->product_id : 0;
				return ['code'=>101,'productId'=>$productId];//此期已经满员
			}
			$productId = $curPeriod->product_id;
		}

		if($productId == '228') {
			// 2元卡禁止 老用户购买
			$regTime = $this->userInfo->created_at;
			if(Version::compare($this->version,'>=','2.0.3')){ // 版本控制
				if($regTime < strtotime("0:00")){
					return ['code'=>10012,'msg'=>'仅限今天注册的新用户参与！'];
				}
			}

		}

		if ($this->isTmp) {
			if (isset($this->tmpCart[$productId])) {
				$this->tmpCart[$productId] += $num;
			}else{
				$this->tmpCart[$productId] = $num;
			}
			$this->cookies->add(new Cookie(['name'=>'_tc','value'=>json_encode($this->tmpCart),'domain' => '.' . DOMAIN]));
			return ['code'=>100];
		}
		if ($this->userInfo->status == 1) {
			return ['code'=>10099,'msg'=>'账户已冻结，如有疑问请联系伙购网客服'];
		}
		$result = Cart::add($this->userId,$productId,$num);

		$result['productId'] = $productId;

		return $result;
	}

	/**
	 * 读取购物车
	 * @return [type] [description]
	 */
	public function actionList(){
		if ($this->isTmp) {
			$list = Cart::tmpInfo($this->tmpCart);
			$data['logined'] = 0;
			$data['list'] = $list;
		}else{
			if ($this->tmpCart && is_array($this->tmpCart)) {
				Cart::delByUid($this->userId);
				foreach ($this->tmpCart as $key => $value) {
					Cart::add($this->userId,$key,$value,true);
				}
				$this->cookies->add(new Cookie(['name'=>'_tc','value'=>'','domain' => '.' . DOMAIN]));
			}
			$is_buy = Yii::$app->request->get('is_buy',null);
			$check = Yii::$app->request->get('check',0);
			$list = Cart::info($this->userId, $check, $is_buy);
			$data['logined'] = 1;
			$data['list'] = $list;
		}

		return $data;
	}

	/**
	 * 修改购物车商品购买数量
	 * @return [type] [description]
	 */
	public function actionChangenum(){
		$num = Yii::$app->request->get('num');
		$productId = Yii::$app->request->get('pid');
		if ($this->isTmp) {
			if (isset($this->tmpCart[$productId])) {
				$this->tmpCart[$productId] = $num;  
                $this->cookies->add(new Cookie(['name'=>'_tc','value'=>json_encode($this->tmpCart),'domain' => '.' . DOMAIN]));
                return ["canBuy"=>true];
			}
            return ["canBuy"=>false];
		}else{
			return Cart::updateNum($this->userId,$productId,$num);
		}
	}

	/**
	 * 修改购物车商品的状态
	 * @return [type] [description]
	 */
	public function actionCheck()
	{
		$pid = explode(',',Yii::$app->request->get('product'));
		$status = explode(',',Yii::$app->request->get('status'));

		if (!$this->userId) {
			return array('code'=>0,'logined'=>0);
		}
		if(in_array('228',$pid)){  // 如果有淘宝2元卡
			$regTime = $this->userInfo->created_at;
			if(Version::compare($this->version,'>=','2.0.3')){ // 版本控制
				if($regTime < strtotime("0:00")){
					return ['code'=>10012,'msg'=>'仅限今天注册的新用户参与！'];
				}
			}
		}

		$rs = Cart::updateBuyStat($this->userId,$pid,$status);

		if ($rs) {
			return array("code"=>100,'invalid'=>$rs['invalid'],'num'=>count($pid));
		}

	}

	/**
	 * 删除商品
	 * @return [type] [description]
	 */
	public function actionDel(){
		$cids = explode(",",Yii::$app->request->get('cid'));
		if ($this->isTmp) {
			$i = 1;
			foreach ($this->tmpCart as $key => $value) {
				foreach ($cids as $v) {
					if ($v == $i) {
						unset($this->tmpCart[$key]);
					}
				}
				$i++;
			}
			$this->cookies->add(new Cookie(['name'=>'_tc','value'=>json_encode($this->tmpCart),'domain' => '.' . DOMAIN]));
		}else{
			$rs = Cart::del($cids);
		}
		return $cids;
	}

	/**
	 * 购物车商品数量
	 * @return [type] [description]
	 */
	public function actionCount()
	{
		if ($this->isTmp) {
			return array('count'=>count($this->tmpCart));
		}else{
			return Cart::count($this->userId);
		}
	}
        
        
    /**
     * 购物车集体设置
     * @param array $data [cid1=>num1,cid2=>num2,...]
     * @return array $rt [cid1=>true,cid2=>false]
     */
    public function actionSave($isClear=true)
    {
        $data = (array) @$_REQUEST["data"]; //Yii::$app->request->post('data',array());

        if ($data)
        {
            if($isClear)       \app\models\Cart::deleteAll(['user_id' => $this->userId]);
            foreach ($data as $productId => &$num)
            {
                $num = Cart::add($this->userId, $productId, $num) == array('code' => 100);
            }
        }
        return $data;
    }

    /**
     * 清除失效商品
     * @return [type] [description]
     */
    public function actionInvalid(){
    	if (!$this->userId) {
    		return false;
    	}
    	$cartInfo = Cart::info($this->userId,true,'1');
    	$invalid = array();
    	foreach ($cartInfo as $key => $value) {
    		if ($value['period_id'] > $value['old_period_id']) {
    			$invalid[] = $value['id'];
    		}
    	}
    	Cart::del($invalid);
    	return array('code'=>100);
    }

}