<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <?php
    if(Yii::$app->user->can('adminPermission'))
    {
        ?>
        <p class="float-right pClassInView" >
            <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
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
            'id',
            'name',
            'email:email',
            'address',
            'city',
            'state',
            'postalCode',
            [
                'attribute'=>'country_id',
                'value'=>$model->country->title,
            ],
            'phone',
            'offline_mode:boolean',
            [
                'attribute'=>'role',
                'value'=>$model->roleLabel,
            ],
            'enable:boolean',
        ],
    ]) ?>

</div>
