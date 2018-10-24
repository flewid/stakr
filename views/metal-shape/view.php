<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\MetalShape */

$this->title = $model->shape;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shapes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-shape-view">

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
            'ID',
            'shape',
            [
                'attribute'=>'author_id',
                'value'=>$model->author->name,
                'visible'=>Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN,
            ],
            [
                'attribute'=>'default',
                'format'=>'boolean',
                'visible'=>Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN,
            ]
        ],
    ]) ?>

</div>
