<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\MetalTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Types');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="table-responsive">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class="float-right pClassInView" >
        <?= Html::a(Yii::t('app', 'Add {modelClass}', [
    'modelClass' => 'Type',
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
            'metalSymbol',
            'metalDescription',
            [
                'attribute'=>'author_id',
                'value'=>function($data) { return $data->author->name; },
                'filter'=>\yii\helpers\ArrayHelper::map(app\models\User::find()->asArray()->all(), 'id', 'name'),
                'visible'=>Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN,
            ],
            [
                'attribute'=>'default',
                'format'=>'boolean',
                'filter'=>['0'=>'No', '1'=>'Yes',],
                'visible'=>Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN,
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                //'template'=>'{view}',
                'buttons' => [
                    'update' => function ($url, $model, $key)
                        {
                            if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                                return Html::a('<i class="fas fa-pencil-alt"></i>', $url, [
                                    'title' => Yii::t('yii', 'Update'),
                                    'data-pjax' => '0',
                                ]);
                        },
                    'delete' => function ($url, $model, $key) {
                            if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                                return Html::a('<i class="far fa-trash-alt"></i>', $url, [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ]);
                        },
                ],

            ],
        ],
    ]); ?>

</div>
