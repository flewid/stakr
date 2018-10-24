<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \app\models\MetalType;
use \app\models\MetalGrade;
use \app\models\MetalShape;
use \app\models\MetalVendors;
use \yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\MetalStackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="table-responsive">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php
    $actionColumn= [
        'class' => 'yii\grid\ActionColumn',
    ];
    $actionColumn['template']='{restore}';
    $actionColumn['buttons']['restore']=function ($url, $model, $key)
    {
        if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
            return Html::a('Restore', $url, [
                'title' => Yii::t('yii', 'Restore'),
                'class' => 'btn btn-primary btn-xs',
                'data-pjax' => '0',
            ]);
    };
    ?>
    <?= GridView::widget([
        'tableOptions'=>['class'=>'table mb-none dataTable',],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => [
            "pageCssClass"=>"page-item",
            "linkOptions"=>["class"=>"page-link"],
            "disabledListItemSubTagOptions" => ['tag' => 'a', 'class' => 'page-link',"href"=>"#"]
        ],
        'columns' => [
            'title',
            'ID',
            [
                'attribute'=>'roll',
                'format'=>'boolean',
                'filter'=>['0'=>'No', '1'=>'Yes',],
            ],
            [
                'attribute'=>'roll',
                'header'=>'Roll contents',
                'format'=>'raw',
                'value'=>function ($model)
                    {
                        if($model->kids)
                        {
                            $loading="<img style=\"display:none; position: absolute;\" src=\"".Yii::$app->request->baseUrl."/static/images/loading.gif\" />";
                            return Html::a('View roll', 'javascript:void(0)', [
                                'title' => Yii::t('yii', 'Down'),
                                'data-id' => $model->ID,
                                'data-pjax' => '0',
                                'class' => 'down btn btn-primary btn-xs',
                            ]).$loading;
                        }
                    },
                'filter'=>false,
            ],
            'metalDescription',
            [
                'attribute'=>'metalSymbol',
                'value'=>function($data) { return $data->type->metalDescription; },
                'filter'=>ArrayHelper::map(MetalType::find()->asArray()->all(), 'ID', 'metalDescription'),
            ],
            [
                'attribute'=>'metalVendor',
                'value'=>function($data) { return $data->vendor->vendorName; },
                'filter'=>ArrayHelper::map(MetalVendors::find()->asArray()->all(), 'ID', 'vendorName'),
            ],
            [
                'attribute'=>'metalShape',
                'value'=>function($data) { return $data->shape->shape; },
                'filter'=>ArrayHelper::map(MetalShape::find()->asArray()->all(), 'ID', 'shape'),
            ],
            [
                'attribute'=>'metalGrade',
                'value'=>function($data) { return $data->grade->metalGrade; },
                'filter'=>ArrayHelper::map(MetalGrade::find()->asArray()->all(), 'ID', 'metalGrade'),
            ],
            [
                'attribute'=>'author_id',
                'value'=>function($data) { return $data->author->name; },
                'filter'=>\yii\helpers\ArrayHelper::map(app\models\User::find()->asArray()->all(), 'id', 'name'),
                'visible'=>Yii::$app->user->can('adminPermission'),
            ],
            [
                'attribute'=>'trade_id',
                'label'=>'Trade',
                'format'=>'raw',
                'visible'=>Yii::$app->controller->action->id=='trade-index',
                'value'=>function($data){
                        if($data->trade_id)
                            return Html::a($data->trade->title, ['metal-stack/view', 'id'=>$data->trade_id,]);
                        if($data->trade_text)
                            return $data->trade_text;
                    },
            ],
            [
                'attribute'=>'sold_price',
                'visible'=>Yii::$app->controller->action->id=='sold-index',
            ],
            $actionColumn,
        ],
    ]);?>
</div>
