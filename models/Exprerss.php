<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "express".
 *
 * @property integer $id
 * @property string name
 * @property string keyword
 * @property integer admin_id
 * @property integer created_at
 * @property integer updated_at
 */
class Express extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'express';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'keyword'], 'required'],
			[['admin_id', 'created_at', 'updated_at', 'id'], 'integer'],
			[['name', 'keyword'], 'string', 'max' => 50]
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => '编号',
			'name' => '快递名称',
			'keyword' => '快递代号',
			'admin' => '添加人',
			'created_at' => '创建时间',
			'updated_at' => '更新时间'
		];
	}
	
	public static function getList($condition = '', $page = 1, $pageSize = 10)
	{
		$query = new Query();
		$query->from('express')
			->select('
            express.id,
            express.name,
            express.keyword,
            express.admin_id,
            express.created_at,
            express.updated_at,
            a.username admin')
			->leftJoin('admin a', 'express.admin_id = a.id');
		if ($condition) {
			if ($condition['account']) {
				$query->andWhere(['or', ['like', 'job_number', $condition['account']], ['like', 'real_name', $condition['account']], ['like', 'username', $condition['account']]]);
			}
			if ($condition['role']) {
				$query->andWhere(['role' => $condition['role']]);
			}
			if ($condition['status'] != 'all') {
				$query->andWhere(['status' => $condition['status']]);
			}
		}
		$countQuery = clone $query;
		$pagination = new Pagination(['totalCount' => $countQuery->count(), 'page' => $page - 1, 'defaultPageSize' => $pageSize]);
		$list = $query->offset($pagination->offset)
			->limit($pagination->limit)
			->all();
		
		return ['rows' => $list, 'total' => $pagination->totalCount];
	}
}
