<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\MetalStack */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Stacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-stack-view">

    <?php
    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
    {
        ?>
        <p class="float-right pClassInView" >
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->ID], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->ID], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    <?php
    }
    ?>
    <div class="clear"></div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute'=>'ID',
                'visible'=>Yii::$app->user->can('adminPermission'),
            ],
            [
                'attribute'=>'title',
                'visible'=>Yii::$app->user->can('adminPermission'),
            ],

            [
                'attribute'=>'metalSymbol',
                'value'=>$model->type->metalDescription,
            ],
            [
                'attribute'=>'metalVendor',
                'value'=>$model->vendor->vendorName,
            ],
            [
                'attribute'=>'metalShape',
                'value'=>$model->shape->shape,
            ],
            [
                'attribute'=>'metalGrade',
                'value'=>$model->grade->metalGrade,
            ],
            [
                'attribute'=>'metalOriginMint',
                'value'=>$model->mint->title,
            ],
            'metalDescription',


            'metalPurchasePrice:dollar',
            'metalQuantity',
            'metalShippingCost:dollar',
            'metalTotalPaid:dollar',
            'metalPurchaseDate:date',
            [
                'attribute'=>'weight',
                'value'=>$model->weight." troy ounces",
            ],
            [
                'attribute'=>'spotPrice',
                'value'=>$model->spotPrice." $/Toz",
            ],
            [
                'attribute'=>'totalSpotPrice',
                'value'=>$model->totalSpotPrice." $/{$model->weight} Toz",
            ],
            [
                'attribute'=>'currentPrice',
                'value'=>$model->currentPrice." $/Toz",
            ],
            'numismatic_value:dollar',
            'total_numismatic_value:dollar',
            'mintMark',
            [
                'attribute'=>'author_id',
                'value'=>$model->author->name,
                'visible'=>Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN,
            ],
        ],
    ]) ?>

</div>
