<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalGrade */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Grade',
]) . ' ' . $model->metalGrade;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Grades'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->metalGrade, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-grade-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
