<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalType */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Type',
]) . ' ' . $model->metalSymbol;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->metalSymbol, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-type-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
