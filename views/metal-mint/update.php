<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalMint */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Mint',
]) . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Mints'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-mint-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
