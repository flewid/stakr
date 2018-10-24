<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\MetalVendors */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Vendor',
]) . ' ' . $model->vendorName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->vendorName, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="metal-vendors-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
