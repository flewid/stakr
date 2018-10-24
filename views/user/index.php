<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="table-responsive">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class="float-right pClassInView" >
        <?= Html::a(Yii::t('app', 'Add {modelClass}', [
    'modelClass' => 'User',
]), ['create'], ['class' => 'btn btn-success' ]) ?>
    </p>

    <?= GridView::widget([
        'tableOptions'=>['class'=>'table mb-none',],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => [
            "pageCssClass"=>"page-item",
            "linkOptions"=>["class"=>"page-link"],
            "disabledListItemSubTagOptions" => ['tag' => 'a', 'class' => 'page-link',"href"=>"#"]
        ],
        'columns' => [
            'name',
            'email:email',

            'address',
            'city',
            'state',
            'postalCode',
            [
                'attribute'=>'country_id',
                'value'=>function($data) { return $data->country->title; },
                'filter'=>\yii\helpers\ArrayHelper::map(\app\models\Country::find()->asArray()->all(), 'id', 'title'),
            ],
            'phone',
            [
                'attribute'=>'role',
                'value'=>function($data) { return $data->roleLabel; },
                'filter'=>\app\models\User::$roleValues,
            ],
            [
                'attribute'=>'enable',
                'format'=>'boolean',
                'filter'=>[1=>'Yes', 0=>'No',],
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
