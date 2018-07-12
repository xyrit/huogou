<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Card;

/**
 * CardSearch represents the model behind the search form about `app\models\Card`.
 */
class CardSearch extends Card
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'card_id', 'home_id', 'money', 'out_type', 'out_user', 'out_id', 'status'], 'integer'],
            [['number', 'password', 'time_out', 'time_start', 'time_end', 'time_used'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Card::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'home_id' => $this->home_id,
            'money' => $this->money,
            'out_type' => $this->out_type,
            'out_user' => $this->out_user,
            'out_id' => $this->out_id,
            'time_out' => $this->time_out,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
            'time_used' => $this->time_used,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'password', $this->password]);

        return $dataProvider;
    }
}
