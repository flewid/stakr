<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\HistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Metal value histories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if(Yii::$app->user->can('adminPermission'))
            echo Html::a('Create metal value history', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => [
            
            "pageCssClass"=>"page-item",
            "linkOptions"=>["class"=>"page-link"],
            "disabledListItemSubTagOptions" => ['tag' => 'a', 'class' => 'page-link',"href"=>"#"]
        ],
        'columns' => [
            'metalValueDate',
            [
                'attribute'=>'metalSymbol',
                'value'=>function($data) { return $data->type->metalDescription; },
                'filter'=>\yii\helpers\ArrayHelper::map(\app\models\MetalType::find()->asArray()->all(), 'ID', 'metalDescription'),
            ],
            'metalValue',

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> Yii::$app->user->can('adminPermission') ? '{view} {update} {delete}':'{view}',
            ],
        ]
    ]); ?>

</div>
