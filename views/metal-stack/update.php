<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalStack */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Metal Stack',
]) . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Stacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-stack-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
