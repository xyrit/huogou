<?php

	namespace app\models;

	use Yii;
	use yii\data\Pagination;
	use app\models\User;

	/**
	* 用户优惠券
	*/
	class UserCoupons extends \yii\db\ActiveRecord
	{
		private static $_tableId;

	    public static function instantiate($row)
	    {
	        return new static(static::$_tableId);
	    }

	    public function __construct($tableId, $config = [])
	    {
	        parent::__construct($config);
	        static::$_tableId = $tableId;
	    }
		
		public static function tableName()
		{
			$tableId = substr(static::$_tableId, 0, 3);
        	return 'user_coupons_' . $tableId;
		}

		public function rules()
	    {
	        return [
	            [['user_id', 'coupon_id', 'code', 'status', 'receive_time','used_time'], 'required'],
	            [['user_id', 'coupon_id'], 'integer']
	        ];
	    }

	    public function attributeLabels(){
	    	return [
	    		'ID' => 'ID',
	    		'user_id' => '用户ID',
	    		'coupon_id' => '优惠券ID',
	    		'code' => '优惠券码',
	    		'status' => '状态',
	    		'receive_time' => '领取时间',
	    		'used_time' => '使用时间'
	    	];
	    }

		/**
		 * @param $tableId
		 * @return \yii\db\ActiveQuery the newly created [[ActiveQuery]] instance.
		 */
		public static function findByTableId($tableId)
		{
			$model = new static($tableId);
			return $model::find();
		}

	    public static function findByUserId($uid)
	    {
	    	$uInfo = User::find()->select('home_id')->where(['id'=>$uid])->asArray()->one();
	    	$tableId = substr($uInfo['home_id'],0,3);
	    	$model = new static($tableId);
        	return $model::find();
	    }

		public static function updateAllByUserId($uid, $attributes, $condition = '', $params = [])
		{
			$uInfo = User::find()->select('home_id')->where(['id'=>$uid])->asArray()->one();
			$tableId = substr($uInfo['home_id'],0,3);
			$model = new static($tableId);
			return $model::updateAll($attributes, $condition, $params);
		}

		/**
	     * 获取用户所有优惠券
	     * @param  int $uid     用户ID
	     * @param  int $page    页数
	     * @param  int $perpage 每页数量
	     * @return [type]          [description]
	     */
	    public static function getUserCoupons($uid,$page,$perpage = '20',$where)
	    {
	    	$query = UserCoupons::findByUserId($uid)->where(['user_id' => $uid]);
	    	if ($where) {
	    		$query = $query->andwhere($where);
	    	}
	    	$query = $query->orderBy('receive_time desc,id desc');
	        $pages = new Pagination(['defaultPageSize' => $perpage, 'totalCount' => $query->count(), 'page' => $page - 1]);

	        $list = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

	        return ['list' => $list, 'total' => $pages->totalCount];
	    }

	    /**
	     * 获取用户所有有效优惠券
	     * @param  int $uid 用户ID
	     * @return [type]      [description]
	     */
	    public static function getValidCoupons($uid)
	    {
	    	// $oldCoupons = 
	    	return UserCoupons::findByUserId($uid)->where(['user_id'=>$uid,'status'=>0])->asArray()->all();
	    }

	    /**
	     * 获取用户优惠券信息
	     * @param  string $code 优惠券码
	     * @return [type]       [description]
	     */
	    public static function getUserCouponByCode($uid,$code)
	    {
	    	return UserCoupons::findByUserId($uid)->where(['user_id'=>$uid,'code'=>$code])->asArray()->one();
	    }

	    /**
	     * 获取用户的优惠券
	     * @param  array $userCouponId 用户的优惠券ID
	     * @return [type]               [description]
	     */
	    public static function getCodeInfo($uid,$userCouponId)
	    {
	    	return UserCoupons::findByUserId($uid)->where(['in','id',$userCouponId])->asArray()->all();
	    }

	    public static function getUserCouponById($uid,$id)
	    {
	    	return UserCoupons::findByUserId($uid)->where(['id'=>$id])->asArray()->one();
	    }
	}