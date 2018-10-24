<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Log */

$this->title = 'Log '.$model->id;
$this->params['breadcrumbs'][] = ['label' => 'Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-view">



    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label'=>'User',
                'attribute'=>'user_id',
                'value'=>$model->user_id ? $model->user->name:null,
            ],
            'action',
            [
                'attribute'=>'metal_id',
                'value'=>$model->metal_id ? $model->metal->title:null,
            ],
            'date',
            'description:raw',
        ],
    ]) ?>

</div>
