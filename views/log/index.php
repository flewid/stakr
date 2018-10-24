<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => [
            "pageCssClass"=>"page-item",
            "linkOptions"=>["class"=>"page-link"],
            "disabledListItemSubTagOptions" => ['tag' => 'a', 'class' => 'page-link',"href"=>"#"]
        ],
        'columns' => [
            //'id',
            //'user_id',
            //'action',
            //'metal_id',
            'description:raw',
            'date',

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> Yii::$app->user->can('adminPermission') ? '{view} {delete}':'{view}',
            ],

        ],
    ]); ?>

</div>
