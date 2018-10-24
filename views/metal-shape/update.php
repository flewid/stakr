<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalShape */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Shape',
]) . ' ' . $model->shape;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shapes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->shape, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-shape-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
